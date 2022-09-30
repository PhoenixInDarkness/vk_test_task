<?php

class Throttler
{
    private int $messageNum;
    private int $seconds;
    private int $position;
    private array $gets = array();

    public function __construct(
        int $messageNum, int $seconds
    )
    {
        $this->messageNum = $messageNum;
        $this->seconds = $seconds;
    }

    /**
     * Реализовал вариант с использоование
     * функций foreach() и array_slice
     * У обеих функций сложность O(n)
     */
    public function throttle(): bool
    {
        $timeEnd = time();
        $timeStart = $timeEnd - $this->seconds;
        $count = 0;
        $lenght = count($this->gets);

        foreach ($this->gets as $get) {
            if ($get > $timeStart && $get <= $timeEnd) {
                $count++;
            }
        }

        if ($count >= $this->messageNum) {
            $result = false;
        } else {
            if($lenght > $this->messageNum) {
                $this->gets = array_slice($this->gets, -$this->messageNum);
            }
            $this->gets[] = time();

            $result = true;
        }

        return $result;
    }

    /**
     * Реализовал вариант без использоования
     * функций foreach() и array_slice
     * Не могу сказать точную сложность
     * функций end() и key(), но предполагаю
     * что она O(1), если это так, то сложность
     * данного решения должна быть O(1)
     */
    public function throttleTwo(): int
    {
        $timeEnd = time();
        $timeStart = $timeEnd - $this->seconds;
        $lenght = count($this->gets);
        $is_run = false;
        end($this->gets);
        $lastKey = key($this->gets);

        if($lenght < $this->messageNum){
            $is_run = true;
        } else {
            $second_element = $this->gets[$lastKey-$this->messageNum+1];
            $is_run = $second_element <= $timeStart;
        }

        if (!$is_run) {
            $result = false;
        } else {
            if ($lenght > $this->messageNum) {
                unset($this->gets[$lastKey - $this->messageNum]);
            }

            $this->gets[] = time();

            $result = true;
        }

        return $result;

    }
}


// Test case example
const SECOND = 1_000_000;

$throttle = new Throttler(2, 10);

// Отправляем 3 сообщения за раз, 3е падает, так как у нас 2 за 10 секунд максимум
$throttle->throttle(); // true
$throttle->throttle(); // true
$throttle->throttle(); // false

// Ждём 10 секунд, теперь снова разрешаем отправлять, так ка кпрощло 10 секунд
usleep(SECOND * 10);
$throttle->throttle(); // true (a)
usleep(SECOND * 5);
$throttle->throttle(); // true (b)
// 3е сообщение за 10 секунд, запрещаем
$throttle->throttle(); // false
usleep(SECOND * 5);
// Прошло 5 секунд со времени последнего успешного сообщения (b), теперь это и предыдущее сообщение укаладываются в
// окно 10 секунд
$throttle->throttle(); // true
