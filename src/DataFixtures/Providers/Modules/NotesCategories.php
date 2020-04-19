<?php
namespace App\DataFixtures\Providers\Modules;

class NotesCategories{

    const KEY_NAME           = 'name';
    const KEY_ICON           = 'icon';
    const KEY_PARENT_NAME    = 'parent_name';

    const CATEGORY_SERVICES = [
        self::KEY_NAME        => 'Services',
        self::KEY_ICON        => 'far fa-handshake',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_PHONE = [
        self::KEY_NAME        => 'Phone',
        self::KEY_ICON        => 'fas fa-phone',
        self::KEY_PARENT_NAME => 'Services',
    ];

    const CATEGORY_BANKING = [
        self::KEY_NAME        => 'Banking',
        self::KEY_ICON        => 'fas fa-money-bill',
        self::KEY_PARENT_NAME => 'Services',
    ];

    const CATEGORY_APPLICATIONS = [
        self::KEY_NAME        => 'Applications',
        self::KEY_ICON        => 'fab fa-product-hunt',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_KEYS = [
        self::KEY_NAME        => 'Keys',
        self::KEY_ICON        => 'fas fa-key',
        self::KEY_PARENT_NAME => 'Applications',
    ];

    const CATEGORY_FINANCES = [
        self::KEY_NAME        => 'Finances',
        self::KEY_ICON        => 'fas fa-money-bill',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_GOALS = [
        self::KEY_NAME        => 'Goals',
        self::KEY_ICON        => 'fas fa-bullseye',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_BUILDING_HOME = [
        self::KEY_NAME        => 'Building home',
        self::KEY_ICON        => 'fas fa-home',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_TRAVEL = [
        self::KEY_NAME        => 'Travel',
        self::KEY_ICON        => 'fas fa-map',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_CAR = [
        self::KEY_NAME        => 'Car',
        self::KEY_ICON        => 'fas fa-car',
        self::KEY_PARENT_NAME => '',
    ];

    const CATEGORY_DIET = [
        self::KEY_NAME        => 'Diet',
        self::KEY_ICON        => 'fas fa-coffee',
        self::KEY_PARENT_NAME => '',
    ];

    /**
     * The order is important, since we first want parents in which we throw subcategories
     */
    const ALL_CATEGORIES = [
      self::CATEGORY_SERVICES,
      self::CATEGORY_PHONE,
      self::CATEGORY_BANKING,
      self::CATEGORY_APPLICATIONS,
      self::CATEGORY_KEYS,
      self::CATEGORY_FINANCES,
      self::CATEGORY_GOALS,
      self::CATEGORY_BUILDING_HOME,
      self::CATEGORY_TRAVEL,
      self::CATEGORY_CAR,
      self::CATEGORY_DIET,
    ];

}