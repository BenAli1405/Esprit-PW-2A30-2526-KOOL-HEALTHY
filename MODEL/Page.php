<?php

class Page
{
    private $slug;
    private $title;
    private $viewFile;

    public function __construct($slug, $title, $viewFile)
    {
        $this->slug = $slug;
        $this->title = $title;
        $this->viewFile = $viewFile;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getViewFile()
    {
        return $this->viewFile;
    }
}
