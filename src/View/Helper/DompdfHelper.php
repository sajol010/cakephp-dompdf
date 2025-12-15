<?php
declare(strict_types=1);

namespace Dompdf\View\Helper;

use Cake\View\Helper;

/**
 * Dompdf helper
 */
class DompdfHelper extends Helper
{
    protected array $helpers = ['Html'];
    protected array $_defaultConfig = [];

    /**
     * Creates a link element for CSS stylesheets.
     *
     * @param string $path The name of a CSS style sheet without extension.
     * @param bool $plugin True to read CSS from plugin assets, false for app assets.
     */
    public function css(string $path, bool $plugin = false): string
    {
        $path = $plugin ? "dompdf/css/{$path}" : "css/{$path}";

        return "<link rel=\"stylesheet\" href=\"{$path}.css\">";
    }

    /**
     * Generate an image tag.
     *
     * @param string $path Path to the image file relative to webroot/img.
     * @param array $options Array of HTML attributes.
     */
    public function image(string $path, array $options = []): string
    {
        $options['src'] = "img/{$path}";
        $options['alt'] = $options['alt'] ?? '';

        return $this->Html->tag('img', null, $options);
    }

    /**
     * Generate a page break.
     */
    public function page_break(): string
    {
        return $this->Html->tag('div', null, ['class' => 'page_break']);
    }

    /**
     * Write page number (use in header or footer).
     */
    public function page_number(): string
    {
        return $this->Html->tag('span', null, ['class' => 'page_number']);
    }
}
