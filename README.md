# Dompdf plugin for CakePHP 5/6

Modernised Dompdf integration for CakePHP, maintained by **sajol010**.

## Requirements
- PHP 8.1 or higher
- CakePHP 5.x (forward compatible with 6.x)
- dompdf 3.x

## Installation

```bash
composer require sajol010/cakephp-dompdf
bin/cake plugin assets symlink --plugin Dompdf
```

Load the plugin in `src/Application.php`:

```php
public function bootstrap(): void
{
    parent::bootstrap();
    $this->addPlugin('Dompdf');
}
```

Enable the `pdf` extension inside `config/routes.php`:

```php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::scope('/', function (RouteBuilder $routes) {
    $routes->setExtensions(['pdf']);
    // ...
});
```

Optionally load the `RequestHandler` component if you want automatic content negotiation.

## Quick start

In a controller action:

```php
public function view(string $filename): void
{
    $this->viewBuilder()
        ->setClassName(\Dompdf\View\PdfView::class)
        ->setLayout('Dompdf.default')
        ->setOptions(['config' => [
            'filename' => $filename,
            'render' => 'browser',
        ]]);
}
```

Create the template at `templates/Yop/pdf/view.php`:

```php
<?php $this->start('header'); ?>
    <p>Header.</p>
<?php $this->end(); ?>

<?php $this->start('footer'); ?>
    <p>Footer.</p>
<?php $this->end(); ?>

<h1>My title</h1>
<p>Banana</p>
<p>Boom !!!</p>
```

Visit `https://dev.local/myproject/yop/view/test.pdf` to render the document.

## Configuration
Set options through `$this->viewBuilder()->setOptions(['config' => [...]])`:

- `filename`: download/display filename.
- `upload_filename`: full path used when `render` is `upload`.
- `render`: `browser`, `download`, `upload`, or `stream` (default `download`).
- `size`: paper size, default `A4`.
- `orientation`: `portrait` (default) or `landscape`.
- `dpi`: image DPI, default `192`.
- `isRemoteEnabled`: allow remote assets, default `true`.
- `paginate`: `false` or an array, see [Pagination](#pagination).
- More: any dompdf option listed at https://github.com/dompdf/dompdf/wiki.

## Views & helper

- Layout lives at `templates/layout/pdf/default.php` and pulls in `webroot/css/dompdf.css`.
- Helper methods (available as `$this->Dompdf`):
  - `css($path, $plugin = false)`
  - `image($path, array $options = [])`
  - `page_break()`
  - `page_number()`

Example page break:

```php
<p>Page 1</p>
<?= $this->Dompdf->page_break(); ?>
<p>Page 2</p>
```

## Render modes

```php
$this->viewBuilder()
    ->setClassName(\Dompdf\View\PdfView::class)
    ->setLayout('Dompdf.default')
    ->setOptions(['config' => [
        'render' => 'browser', // or download|upload|stream
        'filename' => 'mydocument',
    ]]);
```

To stream manually:

```php
use Cake\View\ViewBuilder;

$builder = new ViewBuilder();
$builder->setClassName(\Dompdf\View\PdfView::class)
    ->setLayout('Dompdf.default')
    ->setTemplate('Pdf/pdf/view')
    ->setOptions(['config' => ['render' => 'stream']]);

$view = $builder->build();
$stream = $view->render();
```

## Pagination

With helper (shows the current page number):

```php
<?php $this->start('footer'); ?>
    <p><?= $this->Dompdf->page_number(); ?></p>
<?php $this->end(); ?>
```

With `PdfView` (shows page number and count):

```php
$this->viewBuilder()
    ->setClassName(\Dompdf\View\PdfView::class)
    ->setLayout('Dompdf.default')
    ->setOptions(['config' => [
        'filename' => $filename,
        'render' => 'browser',
        'paginate' => [
            'x' => 550,
            'y' => 5,
        ],
    ]]);
```

Pagination options:
- `x`: left position, default `0`
- `y`: top position, default `0`
- `font`: font family, default `null`
- `size`: font size, default `12`
- `text`: default `"{PAGE_NUM} / {PAGE_COUNT}"`
- `color`: RGB array, default `[0,0,0]`
