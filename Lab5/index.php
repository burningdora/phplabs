<?php

declare(strict_types=1);

/**
 * Интерфейс для хранилища транзакций
 */
interface TransactionStorageInterface {
    public function addTransaction(Transaction $transaction): void;
    public function removeTransactionById(int $id): void;
    public function getAllTransactions(): array;
    public function findById(int $id): ?Transaction;
}

/**
 * Класс, описывающий одну банковскую транзакцию
 */
class Transaction {
    private int $id;
    private DateTime $date;
    private float $amount;
    private string $description;
    private string $merchant;

    public function __construct(int $id, string $date, float $amount, string $description, string $merchant) {
        $this->id = $id;
        $this->date = new DateTime($date);
        $this->amount = $amount;
        $this->description = $description;
        $this->merchant = $merchant;
    }

    public function getId(): int { return $this->id; }
    public function getDate(): DateTime { return $this->date; }
    public function getAmount(): float { return $this->amount; }
    public function getDescription(): string { return $this->description; }
    public function getMerchant(): string { return $this->merchant; }

    /**
     * Возвращает количество дней с момента транзакции
     */
    public function getDaysSinceTransaction(): int {
        $now = new DateTime();
        $interval = $this->date->diff($now);
        return (int)$interval->format('%a');
    }
}

/**
 * Репозиторий для управления коллекцией транзакций
 */
class TransactionRepository implements TransactionStorageInterface {
    private array $transactions = [];

    public function addTransaction(Transaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    public function removeTransactionById(int $id): void {
        $this->transactions = array_filter($this->transactions, fn($t) => $t->getId() !== $id);
    }

    public function getAllTransactions(): array {
        return $this->transactions;
    }

    public function findById(int $id): ?Transaction {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId() === $id) return $transaction;
        }
        return null;
    }
}

/**
 * Класс для выполнения бизнес-логики над транзакциями
 */
class TransactionManager {
    public function __construct(private TransactionStorageInterface $repository) {}

    /** Вычисление общей суммы */
    public function calculateTotalAmount(): float {
        return array_reduce($this->repository->getAllTransactions(), fn($sum, $t) => $sum + $t->getAmount(), 0.0);
    }

    /** Сумма за период */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $sum = 0.0;
        foreach ($this->repository->getAllTransactions() as $t) {
            if ($t->getDate() >= $start && $t->getDate() <= $end) $sum += $t->getAmount();
        }
        return $sum;
    }

    /** Подсчет транзакций по получателю */
    public function countTransactionsByMerchant(string $merchant): int {
        return count(array_filter($this->repository->getAllTransactions(), fn($t) => $t->getMerchant() === $merchant));
    }

    /** Сортировка по дате */
    public function sortTransactionsByDate(): array {
        $data = $this->repository->getAllTransactions();
        usort($data, fn($a, $b) => $a->getDate() <=> $b->getDate());
        return $data;
    }

    /** Сортировка по сумме (убывание) */
    public function sortTransactionsByAmountDesc(): array {
        $data = $this->repository->getAllTransactions();
        usort($data, fn($a, $b) => $b->getAmount() <=> $a->getAmount());
        return $data;
    }
}

/**
 * Класс для вывода данных в HTML
 */
final class TransactionTableRenderer {
    public function render(array $transactions): string {
        $html = "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%; font-family: Arial;'>";
        $html .= "<tr style='background: #ccc;'>
                    <th>ID</th><th>Дата</th><th>Сумма</th><th>Описание</th>
                    <th>Получатель</th><th>Категория</th><th>Дней назад</th>
                  </tr>";
        foreach ($transactions as $t) {
            $html .= "<tr>
                <td>{$t->getId()}</td>
                <td>{$t->getDate()->format('Y-m-d')}</td>
                <td>" . number_format($t->getAmount(), 2) . "</td>
                <td>{$t->getDescription()}</td>
                <td>{$t->getMerchant()}</td>
                <td>Сервис</td> <td>{$t->getDaysSinceTransaction()}</td>
            </tr>";
        }
        return $html . "</table>";
    }
}

// --- ИСПОЛНЕНИЕ ---
$repo = new TransactionRepository();

// Создаем 10 транзакций (Задание 6)
$repo->addTransaction(new Transaction(1, "2024-03-01", 1200.50, "Ужин", "KFC"));
$repo->addTransaction(new Transaction(2, "2024-03-05", 5000.00, "Курсы", "Skillbox"));
$repo->addTransaction(new Transaction(3, "2024-02-10", 300.00, "Кофе", "Surf Coffee"));
$repo->addTransaction(new Transaction(4, "2024-03-15", 15000.00, "Зарплата", "Work"));
$repo->addTransaction(new Transaction(5, "2024-01-20", 240.00, "Проезд", "Метро"));
$repo->addTransaction(new Transaction(6, "2024-03-25", 890.00, "Кино", "Окко"));
$repo->addTransaction(new Transaction(7, "2024-02-28", 4500.00, "Бензин", "Лукойл"));
$repo->addTransaction(new Transaction(8, "2024-03-28", 120.00, "Жвачка", "Пятерочка"));
$repo->addTransaction(new Transaction(9, "2024-03-29", 600.00, "Такси", "Yandex"));
$repo->addTransaction(new Transaction(10, "2024-03-20", 2100.00, "Подарок", "OZON"));

$manager = new TransactionManager($repo);
$renderer = new TransactionTableRenderer();

echo "<h2>Список транзакций (отсортирован по дате):</h2>";
echo $renderer->render($manager->sortTransactionsByDate());

echo "<p><b>Всего потрачено:</b> " . number_format($manager->calculateTotalAmount(), 2) . " руб.</p>";
echo "<p><b>Транзакций в KFC:</b> " . $manager->countTransactionsByMerchant("KFC") . "</p>";