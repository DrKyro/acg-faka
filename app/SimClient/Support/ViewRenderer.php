<?php
declare(strict_types=1);

namespace App\SimClient\Support;

use Kernel\Exception\ViewException;
use Kernel\Util\View;

class ViewRenderer
{
    /**
     * @throws ViewException|\SmartyException
     */
    public static function render(string $template, array $data = []): string
    {
        $data['app']['version'] = config('app')['version'] ?? '1.0.0';
        return View::render($template, $data, BASE_PATH . '/app/SimClient/View', false);
    }
}
