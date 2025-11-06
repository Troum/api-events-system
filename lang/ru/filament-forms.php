<?php

return [

    'builder' => [

        'actions' => [

            'clone' => [
                'label' => 'Клонировать',
            ],

            'add' => [
                'label' => 'Добавить в :label',
            ],

            'add_between' => [
                'label' => 'Вставить между блоками',
            ],

            'delete' => [
                'label' => 'Удалить',
            ],

            'reorder' => [
                'label' => 'Переместить',
            ],

            'move_down' => [
                'label' => 'Переместить вниз',
            ],

            'move_up' => [
                'label' => 'Переместить вверх',
            ],

            'collapse' => [
                'label' => 'Свернуть',
            ],

            'expand' => [
                'label' => 'Развернуть',
            ],

            'collapse_all' => [
                'label' => 'Свернуть все',
            ],

            'expand_all' => [
                'label' => 'Развернуть все',
            ],

        ],

    ],

    'checkbox_list' => [

        'actions' => [

            'deselect_all' => [
                'label' => 'Снять все',
            ],

            'select_all' => [
                'label' => 'Выбрать все',
            ],

        ],

    ],

    'file_upload' => [

        'editor' => [

            'actions' => [

                'cancel' => [
                    'label' => 'Отмена',
                ],

                'drag_crop' => [
                    'label' => 'Режим перетаскивания "обрезка"',
                ],

                'drag_move' => [
                    'label' => 'Режим перетаскивания "перемещение"',
                ],

                'flip_horizontal' => [
                    'label' => 'Отразить изображение по горизонтали',
                ],

                'flip_vertical' => [
                    'label' => 'Отразить изображение по вертикали',
                ],

                'move_down' => [
                    'label' => 'Переместить изображение вниз',
                ],

                'move_left' => [
                    'label' => 'Переместить изображение влево',
                ],

                'move_right' => [
                    'label' => 'Переместить изображение вправо',
                ],

                'move_up' => [
                    'label' => 'Переместить изображение вверх',
                ],

                'reset' => [
                    'label' => 'Сбросить',
                ],

                'rotate_left' => [
                    'label' => 'Повернуть изображение влево',
                ],

                'rotate_right' => [
                    'label' => 'Повернуть изображение вправо',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Установить соотношение сторон :ratio',
                ],

                'save' => [
                    'label' => 'Сохранить',
                ],

                'zoom_100' => [
                    'label' => 'Увеличить изображение до 100%',
                ],

                'zoom_in' => [
                    'label' => 'Приблизить',
                ],

                'zoom_out' => [
                    'label' => 'Отдалить',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Высота',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Поворот',
                    'unit' => 'град',
                ],

                'width' => [
                    'label' => 'Ширина',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Соотношения сторон',

                'no_fixed' => [
                    'label' => 'Свободное',
                ],

            ],

        ],

    ],

    'key_value' => [

        'actions' => [

            'add' => [
                'label' => 'Добавить строку',
            ],

            'delete' => [
                'label' => 'Удалить строку',
            ],

            'reorder' => [
                'label' => 'Переупорядочить строку',
            ],

        ],

        'fields' => [

            'key' => [
                'label' => 'Ключ',
            ],

            'value' => [
                'label' => 'Значение',
            ],

        ],

    ],

    'markdown_editor' => [

        'toolbar_buttons' => [
            'attach_files' => 'Прикрепить файлы',
            'blockquote' => 'Цитата',
            'bold' => 'Жирный',
            'bullet_list' => 'Маркированный список',
            'code_block' => 'Блок кода',
            'heading' => 'Заголовок',
            'italic' => 'Курсив',
            'link' => 'Ссылка',
            'ordered_list' => 'Нумерованный список',
            'redo' => 'Повторить',
            'strike' => 'Зачеркнутый',
            'table' => 'Таблица',
            'undo' => 'Отменить',
        ],

    ],

    'repeater' => [

        'actions' => [

            'add' => [
                'label' => 'Добавить в :label',
            ],

            'delete' => [
                'label' => 'Удалить',
            ],

            'clone' => [
                'label' => 'Клонировать',
            ],

            'reorder' => [
                'label' => 'Переместить',
            ],

            'move_down' => [
                'label' => 'Переместить вниз',
            ],

            'move_up' => [
                'label' => 'Переместить вверх',
            ],

            'collapse' => [
                'label' => 'Свернуть',
            ],

            'expand' => [
                'label' => 'Развернуть',
            ],

            'collapse_all' => [
                'label' => 'Свернуть все',
            ],

            'expand_all' => [
                'label' => 'Развернуть все',
            ],

        ],

    ],

    'rich_editor' => [

        'dialogs' => [

            'link' => [

                'actions' => [
                    'link' => 'Ссылка',
                    'unlink' => 'Удалить ссылку',
                ],

                'label' => 'URL',

                'placeholder' => 'Введите URL',

            ],

        ],

        'toolbar_buttons' => [
            'attach_files' => 'Прикрепить файлы',
            'blockquote' => 'Цитата',
            'bold' => 'Жирный',
            'bullet_list' => 'Маркированный список',
            'code_block' => 'Блок кода',
            'h1' => 'Заголовок',
            'h2' => 'Подзаголовок',
            'h3' => 'Подзаголовок 3',
            'italic' => 'Курсив',
            'link' => 'Ссылка',
            'ordered_list' => 'Нумерованный список',
            'redo' => 'Повторить',
            'strike' => 'Зачеркнутый',
            'underline' => 'Подчеркнутый',
            'undo' => 'Отменить',
        ],

    ],

    'select' => [

        'actions' => [

            'create_option' => [

                'modal' => [

                    'heading' => 'Создать',

                    'actions' => [

                        'create' => [
                            'label' => 'Создать',
                        ],

                        'create_another' => [
                            'label' => 'Создать и создать еще',
                        ],

                    ],

                ],

            ],

            'edit_option' => [

                'modal' => [

                    'heading' => 'Редактировать',

                    'actions' => [

                        'save' => [
                            'label' => 'Сохранить',
                        ],

                    ],

                ],

            ],

        ],

        'boolean' => [
            'true' => 'Да',
            'false' => 'Нет',
        ],

        'loading_message' => 'Загрузка...',

        'max_items_message' => 'Можно выбрать только :count.',

        'no_search_results_message' => 'Нет вариантов, соответствующих вашему поиску.',

        'placeholder' => 'Выберите вариант',

        'searching_message' => 'Поиск...',

        'search_prompt' => 'Начните вводить для поиска...',

    ],

    'tags_input' => [
        'placeholder' => 'Новый тег',
    ],

    'wizard' => [

        'actions' => [

            'previous_step' => [
                'label' => 'Назад',
            ],

            'next_step' => [
                'label' => 'Далее',
            ],

        ],

    ],

];

