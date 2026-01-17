<?php

namespace App\Services;

/**
 * ChatSessionMemoryService
 * 
 * Service untuk menyimpan conversation state dalam session.
 * Memungkinkan AI melanjutkan percakapan yang terputus.
 * 
 * State yang disimpan:
 * - pending_targets: Array of target items yang belum diproses
 * - awaiting_income_input: Boolean, apakah sedang menunggu input income
 * - last_intent: Intent terakhir yang diproses
 */
class ChatSessionMemoryService
{
    /**
     * Session key prefix
     */
    protected const SESSION_PREFIX = 'ai_chat_memory_';

    /**
     * Get session key for specific user
     */
    protected function getKey(string $key): string
    {
        return self::SESSION_PREFIX . $key;
    }

    /**
     * Check if awaiting income input from user
     */
    public function isAwaitingIncomeInput(): bool
    {
        return session($this->getKey('awaiting_income_input'), false);
    }

    /**
     * Set awaiting income input flag
     */
    public function setAwaitingIncomeInput(bool $value): void
    {
        session([$this->getKey('awaiting_income_input') => $value]);
    }

    /**
     * Get pending targets from session
     * 
     * @return array Array of targets: [['name' => 'laptop', 'amount' => 7000000], ...]
     */
    public function getPendingTargets(): array
    {
        return session($this->getKey('pending_targets'), []);
    }

    /**
     * Set pending targets in session
     * 
     * @param array $targets Array of targets
     */
    public function setPendingTargets(array $targets): void
    {
        session([$this->getKey('pending_targets') => $targets]);
    }

    /**
     * Get total amount from pending targets
     */
    public function getPendingTargetTotal(): int
    {
        $targets = $this->getPendingTargets();
        return array_sum(array_column($targets, 'amount'));
    }

    /**
     * Clear all pending state (after simulation complete)
     */
    public function clearPendingState(): void
    {
        session()->forget($this->getKey('awaiting_income_input'));
        session()->forget($this->getKey('pending_targets'));
        session()->forget($this->getKey('last_intent'));
    }

    /**
     * Save complete state for goal simulation
     * 
     * @param array $targets Array of targets
     */
    public function savePendingGoalSimulation(array $targets): void
    {
        $this->setPendingTargets($targets);
        $this->setAwaitingIncomeInput(true);
        $this->setLastIntent('goal_simulation');
    }

    /**
     * Get last intent
     */
    public function getLastIntent(): ?string
    {
        return session($this->getKey('last_intent'));
    }

    /**
     * Set last intent
     */
    public function setLastIntent(string $intent): void
    {
        session([$this->getKey('last_intent') => $intent]);
    }

    /**
     * Check if has pending goal simulation
     */
    public function hasPendingGoalSimulation(): bool
    {
        return $this->isAwaitingIncomeInput() && !empty($this->getPendingTargets());
    }

    /**
     * Format pending targets as readable string
     */
    public function formatPendingTargetsString(): string
    {
        $targets = $this->getPendingTargets();

        if (empty($targets)) {
            return '';
        }

        $items = [];
        foreach ($targets as $target) {
            $amount = 'Rp ' . number_format($target['amount'], 0, ',', '.');
            $name = ucfirst($target['name']);
            $items[] = "**{$name}** ({$amount})";
        }

        if (count($items) === 1) {
            return $items[0];
        }

        $last = array_pop($items);
        return implode(', ', $items) . ' dan ' . $last;
    }

    /**
     * Get debug info for current state
     */
    public function getDebugInfo(): array
    {
        return [
            'awaiting_income_input' => $this->isAwaitingIncomeInput(),
            'pending_targets' => $this->getPendingTargets(),
            'pending_total' => $this->getPendingTargetTotal(),
            'last_intent' => $this->getLastIntent(),
        ];
    }
}
