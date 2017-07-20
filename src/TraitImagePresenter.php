<?php

namespace NAttreid\ImageStorage;

use Nette\Application\UI\ITemplate;

trait TraitImagePresenter
{

	/** @var ImageStorage */
	protected $imageStorage;

	public function injectImageStorage(ImageStorage $imageStorage)
	{
		$this->imageStorage = $imageStorage;
	}

	/**
	 * @param ITemplate $template
	 * @return ITemplate
	 */
	public function createTemplate($template = NULL)
	{
		$template = $template ?: parent::createTemplate();

		$template->imageStorage = $this->imageStorage;

		return $template;
	}

}
