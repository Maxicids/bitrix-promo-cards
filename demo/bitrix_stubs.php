<?php
/**
 * Минимальные заглушки API 1С-Битрикс — только то, что использует шаблон promo_cards.
 * Нужны исключительно для демо-рендера без установленной CMS. На сайт не деплоятся.
 */

namespace Bitrix\Main\Localization {
    class Loc
    {
        private static array $messages = [];
        public static string $lang = 'ru';

        public static function loadMessages(string $file): void
        {
            $langFile = dirname($file) . '/lang/' . self::$lang . '/' . basename($file);
            if (is_file($langFile)) {
                $MESS = [];
                include $langFile;
                self::$messages = array_merge(self::$messages, $MESS);
            }
        }

        public static function getMessage(string $code): ?string
        {
            return self::$messages[$code] ?? null;
        }
    }
}

namespace {
    const B_PROLOG_INCLUDED = true;
    const BX_RESIZE_IMAGE_PROPORTIONAL = 1;

    /** Разбор даты в формате сайта (d.m.Y [H:i[:s]]). */
    function MakeTimeStamp(string $value)
    {
        foreach (['d.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y'] as $format) {
            $date = DateTime::createFromFormat('!' . $format, $value);
            if ($date !== false) {
                return $date->getTimestamp();
            }
        }

        return false;
    }

    function htmlspecialcharsbx($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    function GetMessage(string $code): ?string
    {
        return \Bitrix\Main\Localization\Loc::getMessage($code);
    }

    class CFile
    {
        public static function ResizeImageGet($file, array $size, int $mode = BX_RESIZE_IMAGE_PROPORTIONAL, bool $initSizes = false): array
        {
            return ['src' => $file['SRC'], 'width' => $size['width'], 'height' => $size['height']];
        }
    }

    class CIBlock
    {
        public static function GetArrayByID($iblockId, string $key = ''): string
        {
            return $key;
        }
    }

    /** Упрощённый CBitrixComponentTemplate: подключает result_modifier.php и template.php. */
    class CBitrixComponentTemplate
    {
        public function __construct(private string $folder)
        {
        }

        public function render(array $arParams, array $arResult): string
        {
            $templateFolder = $this->folder;

            (function () use (&$arResult, $arParams) {
                include $this->folder . '/result_modifier.php';
            })();

            ob_start();
            include $this->folder . '/template.php';

            return ob_get_clean();
        }

        public function AddEditAction($id, $link, $title = '', $params = []): void
        {
        }

        public function AddDeleteAction($id, $link, $title = '', $params = []): void
        {
        }

        public function GetEditAreaId($id): string
        {
            return 'bx_promo_' . $id;
        }
    }
}
