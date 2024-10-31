<?php

namespace Pmpr\Plugin\Pmpr\Component;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;
use Pmpr\Plugin\Pmpr\Traits\HelperTrait;

/**
 * Class Item
 * @package Pmpr\Plugin\Pmpr\Component
 */
class Item
{
    use HelperTrait;

    /**
     * @var array|object
     */
    protected $data = [];

    /**
     * @var array|object
     */
    protected $composer = [];

    /**
     * @var int|string|null
     */
    protected $rating = 0;

    /**
     * @var int|mixed|string
     */
    protected $ratingCount = 0;

    /**
     * @var int|mixed|string
     */
    protected $activeInstall = 0;

    /**
     * @var string|null
     */
    protected ?string $state = 'coming_soon';

    /**
     * @var string|null
     */
    protected ?string $name = '';

    /**
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * @var string|null
     */
    protected ?string $image = null;

    /**
     * @var string|null
     */
    protected ?string $version = null;

    /**
     * @var string
     */
    protected string $price = '0';

    /**
     * @var array|object|null
     */
    protected $backlinkModal = [];

    /**
     * @var string|null
     */
    protected ?string $imageDate = null;

    /**
     * @var string|null
     */
    protected ?string $lastUpdate = null;

    /**
     * @var string|null
     */
    protected ?string $permalink = null;

    /**
     * @var bool
     */
    protected bool $freeVersion = false;

    /**
     * @var string|null
     */
    protected ?string $newVersion = null;

    /**
     * @var bool
     */
    protected bool $required = false;

    /**
     * @var string|null
     */
    protected ?string $publishDue = null;

    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * Item constructor.
     *
     * @param $component
     */
    public function __construct($component)
    {
        if (is_array($component)) {

            if ('fa_IR' === get_user_locale()) {

                $title       = $component['fa_title'] ?? '';
                $description = $component['fa_description'] ?? '';
            } else {

                $title       = $component['en_title'] ?? '';
                $description = $component['en_description'] ?? '';
            }

            $component[Constants::TITLE]       = $title;
            $component[Constants::DESCRIPTION] = $description;

            $this->data = $component;
            foreach ($component as $key => $value) {

                $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                if ($value && property_exists($this, $key)) {

                    $this->{$key} = $value;
                }
            }
        } else if (is_string($component)) {

            $this->name = $component;
        }

        if (!$this->getName()) {

            wp_die('component name not specified.');
        }

        $composerPath = trailingslashit($this->getHelper()->getComponent()->getRootPathByType($this->getType())) . trailingslashit($this->getName()) . 'composer.json';
        if (file_exists($composerPath)) {

            $fields = wp_json_file_decode($composerPath);
            if (is_array($fields) || is_object($fields)) {

                $this->composer = (array)$fields;
            }
        }

    }

    /**
     * @return array|object
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function getComposerField($key, $default = null)
    {
        return $this->getHelper()->getType()->arrayGetItem($this->getComposer(), $key, $default);
    }

    /**
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int|mixed|string
     */
    public function getActiveInstall()
    {
        return $this->activeInstall;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return int|string|null
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param string $context
     *
     * @return string|null
     */
    public function getVersion(string $context = ''): ?string
    {
        if ($this->getHelper()->getComponent()->isUseTestRepository()) {

            $version = $this->getComposerField('timestamp', '0');
            if ('view' === $context) {
                $version = wp_date('Y-m-d H:i:s', $version);
            }
        } else {

            $version = $this->getComposerField('tag', $this->version);
        }

        return $version;
    }

    /**
     * @param string $context
     *
     * @return string|null
     */
    public function getNewVersion(string $context = ''): ?string
    {
        $version = $this->newVersion;

        if ('view' === $context && is_numeric($version) && $version > 0
            && $this->getHelper()->getComponent()->isUseTestRepository()) {
            $version = wp_date('Y-m-d H:i:s', $version);
        }

        return $version;
    }

    /**
     * @return string|null
     */
    public function getPublishDue(): ?string
    {
        return $this->publishDue;
    }

    /**
     * @return int|mixed|string
     */
    public function getRatingCount()
    {
        return $this->ratingCount;
    }

    /**
     * @return string|null
     */
    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    /**
     * @return string|null
     */
    public function getPermalink(): ?string
    {
        return $this->permalink;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @return string|null
     */
    public function getImageDate(): ?string
    {
        return $this->imageDate;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        if (!$this->title) {

            $this->title = $this->getName();
        }

        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getHelper()->getComponent()->getType($this->getName(), false);
    }

    /**
     * @return string
     */
    public function getTypeSlug(): string
    {
        return $this->getHelper()->getComponent()->getType($this->getName());
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        $type = $this->getTypeSlug();
        $name = str_replace("wp-{$type}-", '', $this->getName());
        return "pr__{$type}__" . str_replace('-', '_', $name);
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->getHelper()->getComponent()->isInstalled($this->getName());
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->getState() === 'publish';
    }

    /**
     * @return bool
     */
    public function isComingSoon(): bool
    {
        return $this->getState() === 'coming_soon';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $name = $this->getName();
        if (in_array($this->getType(), [Constants::COMMON, Constants::UTILITY], true)) {

            $isActive = $this->getHelper()->getComponent()->isInstalled($name);
        } else {

            $isActive = $this->getHelper()->getComponent()->isActive($name);
        }
        return $isActive;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getImageURL(): string
    {
        return $this->getHelper()->getAsset()->maybeSave($this->getImage(), 'img/component/', $this->getName(), "-{$this->getImageDate()}");
    }

    /**
     * @param string $target
     *
     * @return string
     */
    private function generateLink(string $target): string
    {
        $repository = 'pmpr';
        if ($this->getHelper()->getComponent()->isUseTestRepository()) {
            $repository = 'pmpr-test';
        }
        return "https://raw.githubusercontent.com/{$repository}/{$this->getName()}/main/{$target}";
    }

    /**
     * @return string
     */
    public function getDownloadLink(): string
    {
        return $this->generateLink('source.zip');
    }

    /**
     * @return string
     */
    public function getInfoLink(): string
    {
        return $this->generateLink('composer.json');
    }

    /**
     * @return bool
     */
    public function hasFreeVersion(): bool
    {
        return $this->freeVersion;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return array|object|null
     */
    public function getBacklinkModal()
    {
        return $this->backlinkModal;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        $name = $this->getName();

        $HTMLHelper      = $this->getHelper()->getHTML();
        $componentHelper = $this->getHelper()->getComponent();

        if (!$componentHelper->isInstalled($name)) {

            if ($this->isComingSoon()) {

                $actions[] = $this->createAction([
                    Constants::TITLE  => __('Coming Soon', PR__PLG__PMPR),
                    Constants::ENABLE => false,
                ]);
            } else if ($backlinkModal = $this->getBacklinkModal()) {

                $typeHelper = $this->getHelper()->getType();

                $actions[] = $HTMLHelper->createModal([
                    Constants::NAME    => 'install-component',
                    Constants::TITLE   => $typeHelper->arrayGetItem($backlinkModal, Constants::TITLE),
                    Constants::PREFIX  => $this->getName() . '_modal',
                    Constants::CONTENT => wpautop($typeHelper->arrayGetItem($backlinkModal, Constants::TEXT)),
                    Constants::BUTTONS => [
                        Constants::CANCEL,
                        Constants::INSTALL => $this->createAction([
                            Constants::JOB   => Constants::INSTALL,
                            Constants::TITLE => __('Accept & Install', PR__PLG__PMPR),
                            Constants::ATTRS => [
                                'rol'   => 'button',
                                'type'  => 'button',
                                'class' => 'pr-btn btn-primary',
                            ],
                        ]),
                    ],
                ], __('Install Now', PR__PLG__PMPR), ['class' => 'button button-default']);

            } else {

                $actions[] = $this->createAction([
                    Constants::JOB   => Constants::INSTALL,
                    Constants::TITLE => __('Install Now', PR__PLG__PMPR),
                ]);
            }
        } else if ($this->hasUpdate()) {

            $actions[] = $this->createAction([
                Constants::JOB   => 'update',
                Constants::TITLE => __('Update', PR__PLG__PMPR),
            ]);

        } else if ($componentHelper->isActive($name)) {

            $actions[] = $this->createAction([
                Constants::TITLE  => __('Active', PR__PLG__PMPR),
                Constants::ENABLE => false,
            ]);
        } else {

            $actions[] = $this->createAction([
                Constants::JOB   => 'activate',
                Constants::COLOR => Constants::PRIMARY,
                Constants::TITLE => __('Activation', PR__PLG__PMPR),
            ]);
        }

        if ($this->isPublished()
            && ($permalink = $this->getPermalink())) {

            $actions[] = $HTMLHelper->createElement('a', [
                'href'       => $permalink,
                'aria-label' => sprintf(__('More information about %s', PR__PLG__PMPR), $name),
                'data-title' => $name,
                'target'     => '_blank',
            ], __('More Details', PR__PLG__PMPR));

            if ($price = $this->getPrice()) {

                $actions[] = $HTMLHelper->createStrong($price, [
                    'class' => 'font-16' . ($this->hasFreeVersion() ? ' text-danger' : ''),
                ]);
            }
        }

        return $actions;
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function createAction(array $args = []): string
    {
        $typeHelper = $this->getHelper()->getType();

        $job    = $typeHelper->arrayGetItem($args, Constants::JOB);
        $title  = $typeHelper->arrayGetItem($args, Constants::TITLE);
        $enable = $typeHelper->arrayGetItem($args, Constants::ENABLE, true);

        $color = $args[Constants::COLOR] ?? 'default';

        $args = $typeHelper->parseArgs($args, [
            Constants::JOB   => $job,
            Constants::NAME  => $this->getName(),
            Constants::SLUG  => $this->getSlug(),
            Constants::TYPE  => $this->getType(),
            Constants::COLOR => 'default',
            Constants::TITLE => $title,
            Constants::ATTRS => [
                'rol'   => 'button',
                'type'  => 'button',
                'class' => "button button-{$color}" . ($enable ? '' : ' disabled'),
            ],
        ]);

        return $this->getHelper()->getHTML()->createAction($args);
    }

    /**
     * @param $new
     *
     * @return bool
     */
    public function hasUpdate($new = null): bool
    {
        $has = false;
        if ($this->getHelper()->getComponent()->isUseTestRepository()) {

            $timestamp = $this->getVersion();
            if (!$new) {
                $new = $this->getNewVersion();
            }

            $has = $new > $timestamp;
        } else {

            $version = $this->getVersion();
            if (!$new) {
                $new = $this->getNewVersion();
            }

            if ($new && $version !== $new) {

                // TODO: it's can get error, if data cleared by user and new data fetched
                $has = $new && version_compare($new, $version, '>');
            }
        }
        return $has;
    }
}