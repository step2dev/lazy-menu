<?php

namespace Step2dev\LazyMenu\Navigation\Menu;

use Closure;
use InvalidArgumentException;
use LogicException;

class MenuRegistry
{
    /** @var array<string, array{contributor: Closure, priority: int, before: ?string, after: ?string, index: int}> */
    private array $contributors = [];

    /** @var array<string, array{priority: ?int, before: ?string, after: ?string}> */
    private array $overrides = [];

    /** @var list<Closure>|null */
    private ?array $ordered = null;

    private ?string $view = null;

    public function useView(string $view): void
    {
        $this->view = $view;
    }

    public function view(): ?string
    {
        return $this->view;
    }

    public function register(
        Closure $contributor,
        ?string $id = null,
        int $priority = 0,
        ?string $before = null,
        ?string $after = null,
        ?string $group = null,
    ): void {
        $this->validatePosition($before, $after);

        if ($group !== null) {
            $callback = $contributor;
            $contributor = static function (MenuManager $menu) use ($callback, $group): void {
                $menu->group($group);
                $callback($menu);
            };
        }

        $index = count($this->contributors);
        $id ??= '@anonymous:'.$index;

        if (isset($this->contributors[$id])) {
            throw new InvalidArgumentException("Menu contributor [{$id}] is already registered.");
        }

        $this->contributors[$id] = compact('contributor', 'priority', 'before', 'after', 'index');
        $this->ordered = null;
    }

    public function order(string $id, ?int $priority = null, ?string $before = null, ?string $after = null): void
    {
        $this->validatePosition($before, $after);
        $this->overrides[$id] = compact('priority', 'before', 'after');
        $this->ordered = null;
    }

    /** @return list<Closure> */
    public function contributors(): array
    {
        if ($this->ordered !== null) {
            return $this->ordered;
        }

        $entries = $this->contributors;
        $edges = [];
        $indegree = array_fill_keys(array_keys($entries), 0);

        foreach ($entries as $id => &$entry) {
            if (isset($this->overrides[$id])) {
                $override = $this->overrides[$id];
                $entry['priority'] = $override['priority'] ?? $entry['priority'];
                $entry['before'] = $override['before'];
                $entry['after'] = $override['after'];
            }

            $target = $entry['before'] ?? $entry['after'];
            if ($target === null || ! isset($entries[$target])) {
                continue;
            }

            if ($target === $id) {
                throw new LogicException("Menu contributor [{$id}] cannot be positioned relative to itself.");
            }

            $from = $entry['before'] !== null ? $id : $target;
            $to = $entry['before'] !== null ? $target : $id;
            $edges[$from][] = $to;
            $indegree[$to]++;
        }
        unset($entry);

        $sorted = [];
        while (count($sorted) < count($entries)) {
            $ready = array_keys(array_filter($indegree, static fn (int $degree): bool => $degree === 0));
            if ($ready === []) {
                throw new LogicException('Menu contributor positioning contains a cycle.');
            }

            usort($ready, static fn (string $a, string $b): int => ($entries[$a]['priority'] <=> $entries[$b]['priority'])
                ?: ($entries[$a]['index'] <=> $entries[$b]['index'])
            );
            $id = $ready[0];
            $sorted[] = $entries[$id]['contributor'];
            unset($indegree[$id]);
            foreach ($edges[$id] ?? [] as $to) {
                $indegree[$to]--;
            }
        }

        return $this->ordered = $sorted;
    }

    private function validatePosition(?string $before, ?string $after): void
    {
        if ($before !== null && $after !== null) {
            throw new InvalidArgumentException('Specify either before or after for a menu contributor.');
        }
    }
}
