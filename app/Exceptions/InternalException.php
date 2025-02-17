<?php

namespace App\Exceptions;

use Exception;

class InternalException extends Exception
{
    protected bool $renderView = false;
    protected string $viewName;
    protected array $viewData = [];

    public function renderView(string $view, array $data = []): static
    {
        $this->renderView = true;
        $this->viewName = $view;
        $this->viewData = $data;
        return $this;
    }

    public function shouldRenderView(): bool
    {
        return $this->renderView;
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }
}

