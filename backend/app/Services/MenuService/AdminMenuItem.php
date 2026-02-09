<?php

declare(strict_types=1);

namespace App\Services\MenuService;

/**
 * Représente un élément du menu d'administration.
 * Inspiré de l'usage dans AdminMenuService et les vues sidebar.
 */
final class AdminMenuItem
{
    public string $id;

    public string $label;

    public ?string $icon = null;

    public ?string $iconClass = null; // fallback si on veut une classe custom

    public ?string $route = null;

    public bool $active = false;

    /** @var AdminMenuItem[] */
    public array $children = [];

    public ?string $htmlData = null; // Contenu HTML direct (bypass rendu standard)

    public ?string $itemStyles = null; // Styles inline optionnels

    public ?string $target = null; // _blank etc.

    /**
     * @param  array  $data  Clés acceptées: id,label,icon,iconClass,route,active,children,htmlData,itemStyles,target
     */
    public function __construct(array $data = [])
    {
        $this->id = (string) ($data['id'] ?? uniqid('menu_'));
        $this->label = (string) ($data['label'] ?? '');
        $this->icon = $data['icon'] ?? null;
        $this->iconClass = $data['iconClass'] ?? null;
        $this->route = $data['route'] ?? null;
        $this->active = (bool) ($data['active'] ?? false);
        $this->htmlData = $data['htmlData'] ?? null;
        $this->itemStyles = $data['itemStyles'] ?? null;
        $this->target = $data['target'] ?? null;

        // Normalisation des enfants si fournis
        if (! empty($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                if ($child instanceof self) {
                    $this->children[] = $child;
                } elseif (is_array($child)) {
                    $this->children[] = new self($child);
                }
            }
        }
    }

    /**
     * Ajoute un enfant au menu.
     */
    public function addChild(self $item): self
    {
        $this->children[] = $item;

        return $this;
    }

    /**
     * Définit l'état actif.
     */
    public function setActive(bool $state = true): self
    {
        $this->active = $state;

        return $this;
    }
}
