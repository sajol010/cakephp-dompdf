<?php
declare(strict_types=1);

namespace Dompdf\View;

use Cake\View\View;
use Cake\View\ViewBuilder;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

class PdfView extends View
{
    /**
     * Default Dompdf configuration.
     */
    protected array $dompdfConfig = [
        'dpi' => 192,
        'isRemoteEnabled' => true,
        'size' => 'A4',
        'orientation' => 'portrait',
        'render' => 'download',
        'filename' => 'document',
        'paginate' => false,
    ];

    protected ?Dompdf $pdf = null;

    protected array $paginationDefaults = [
        'x' => 0,
        'y' => 0,
        'font' => null,
        'size' => 12,
        'text' => '{PAGE_NUM} / {PAGE_COUNT}',
        'color' => [0, 0, 0],
    ];

    public function __construct(ViewBuilder $builder)
    {
        parent::__construct($builder);

        $config = (array)$builder->getOption('config');
        if ($config) {
            $this->dompdfConfig = array_merge($this->dompdfConfig, $config);
        }
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->loadHelper('Dompdf.Dompdf');
    }

    public function render(?string $template = null, ?string $layout = null): string
    {
        $this->pdf = $this->buildRenderer();
        $this->pdf->setPaper($this->dompdfConfig['size'], $this->dompdfConfig['orientation']);

        $pdf = $this->pdf;
        $this->set(compact('pdf'));

        $this->pdf->loadHtml(parent::render($template, $layout));
        $this->pdf->render();

        if (is_array($this->dompdfConfig['paginate'])) {
            $this->paginate();
        }

        return match ($this->dompdfConfig['render']) {
            'browser', 'stream' => $this->pdf->output(),
            'upload' => $this->saveUpload(),
            default => $this->pdf->stream($this->dompdfConfig['filename']),
        };
    }

    /**
     * Write pagination on the pdf.
     */
    private function paginate(): void
    {
        $canvas = $this->pdf?->get_canvas();
        if ($canvas === null) {
            return;
        }

        $config = array_merge($this->paginationDefaults, $this->dompdfConfig['paginate']);
        $canvas->page_text($config['x'], $config['y'], $config['text'], $config['font'], $config['size'], $config['color']);
    }

    private function saveUpload(): string
    {
        $output = $this->pdf?->output() ?? '';
        $target = $this->dompdfConfig['upload_filename'] ?? null;

        if (!$target) {
            throw new RuntimeException('Missing "upload_filename" in dompdf config.');
        }

        if (file_put_contents($target, $output) === false) {
            throw new RuntimeException(sprintf('Unable to write PDF to "%s".', $target));
        }

        return $output;
    }

    /**
     * Build a Dompdf instance with vetted options (compatible with Dompdf 3.x).
     */
    private function buildRenderer(): Dompdf
    {
        $options = new Options();
        $optionKeys = [
            'dpi',
            'isRemoteEnabled',
            'isHtml5ParserEnabled',
            'isPhpEnabled',
            'isJavascriptEnabled',
            'isFontSubsettingEnabled',
            'defaultFont',
            'defaultMediaType',
            'tempDir',
            'fontDir',
            'fontCache',
            'chroot',
            'logOutputFile',
        ];

        foreach ($optionKeys as $key) {
            if (array_key_exists($key, $this->dompdfConfig)) {
                $options->set($key, $this->dompdfConfig[$key]);
            }
        }

        return new Dompdf($options);
    }
}
