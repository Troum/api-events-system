<?php

return [

    'columns' => [

        'tags' => [
            'more' => 'и еще :count',
        ],

        'messages' => [
            'copied' => 'Скопировано',
        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'Выбрать/снять все элементы для массовых действий.',
        ],

        'bulk_select_record' => [
            'label' => 'Выбрать элемент :key для массовых действий.',
        ],

        'bulk_select_group' => [
            'label' => 'Выбрать группу :title для массовых действий.',
        ],

        'search' => [
            'label' => 'Поиск',
            'placeholder' => 'Поиск',
            'indicator' => 'Поиск',
        ],

    ],

    'summary' => [

        'heading' => 'Итого',

        'subheadings' => [
            'all' => 'Все :label',
            'group' => 'Итого :group',
            'page' => 'Эта страница',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'Среднее',
            ],

            'count' => [
                'label' => 'Количество',
            ],

            'sum' => [
                'label' => 'Сумма',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'Завершить переупорядочивание записей',
        ],

        'enable_reordering' => [
            'label' => 'Переупорядочить записи',
        ],

        'filter' => [
            'label' => 'Фильтр',
        ],

        'group' => [
            'label' => 'Группировать',
        ],

        'open_bulk_actions' => [
            'label' => 'Открыть действия',
        ],

        'toggle_columns' => [
            'label' => 'Переключить столбцы',
        ],

    ],

    'empty' => [

        'heading' => 'Нет :model',

        'description' => 'Создайте :model для начала работы.',

    ],

    'filters' => [

        'actions' => [

            'remove' => [
                'label' => 'Удалить фильтр',
            ],

            'remove_all' => [
                'label' => 'Удалить все фильтры',
                'tooltip' => 'Удалить все фильтры',
            ],

            'reset' => [
                'label' => 'Сбросить',
            ],

        ],

        'heading' => 'Фильтры',

        'indicator' => 'Активные фильтры',

        'multi_select' => [
            'placeholder' => 'Все',
        ],

        'select' => [
            'placeholder' => 'Все',
        ],

        'trashed' => [

            'label' => 'Удаленные записи',

            'only_trashed' => 'Только удаленные записи',

            'with_trashed' => 'С удаленными записями',

            'without_trashed' => 'Без удаленных записей',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'Группировать по',
                'placeholder' => 'Группировать по',
            ],

            'direction' => [

                'label' => 'Направление группировки',

                'options' => [
                    'asc' => 'По возрастанию',
                    'desc' => 'По убыванию',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'Перетащите записи в нужном порядке.',

    'selection_indicator' => [

        'selected_count' => 'Выбрано: 1 запись|Выбрано: :count записи|Выбрано: :count записей',

        'actions' => [

            'select_all' => [
                'label' => 'Выбрать все :count',
            ],

            'deselect_all' => [
                'label' => 'Снять все',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'Сортировать по',
            ],

            'direction' => [

                'label' => 'Направление сортировки',

                'options' => [
                    'asc' => 'По возрастанию',
                    'desc' => 'По убыванию',
                ],

            ],

        ],

    ],

];

