<?php

namespace NAttreid\ImageStorage;

use Nette\Application\UI\Template;

trait TraitImagePresenter
{

	/** @var ImageStorage */
	protected $imageStorage;

	public function injectImageStorage(ImageStorage $imageStorage): void
	{
		$this->imageStorage = $imageStorage;
	}

	public function createTemplate(): Template
	{
		$template = parent::createTemplate();

		$template->imageStorage = $this->imageStorage;

		return $template;
	}

}
