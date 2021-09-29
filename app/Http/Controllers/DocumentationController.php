<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    const Pages = [
        'README.md' => 'Documentation',
        'Installation.md' => 'Installation',
        'Configuration.md' => 'Configurations',
        'Commands.md' => 'Commands',
        'Broadcasting.md' => 'Broadcasting',
        'ChatBots.md' => 'Chat Bots',
        'Calling.md' => 'Calling',
        'Composer.md' => 'Messenger Composer',
        'Helpers.md' => 'Helpers and Facades',
        'Events.md' => 'Events',
    ];

    /**
     * @return View
     */
    public function index(): View
    {
        return $this->render('README.md');
    }

    /**
     * @param  string  $page
     * @return View
     */
    public function render(string $page): View
    {
        if (! array_key_exists($page, self::Pages)) {
            throw new NotFoundHttpException('Unable to locate the documentation you requested.');
        }

        return view('docs.portal')->with([
            'title' => self::Pages[$page],
            'markdown' => $this->parse($page),
        ]);
    }

    /**
     * @param  string  $markdownFile
     * @return HtmlString
     *
     * @throws NotFoundHttpException
     */
    private function parse(string $markdownFile): HtmlString
    {
        $file = storage_path('app/messenger-docs/'.$markdownFile);

        if (! file_exists($file)) {
            throw new NotFoundHttpException("The { $markdownFile } markdown file was not found. Please run the command 'php artisan download:docs' to download it.");
        }

        return Cache::remember(
            $markdownFile,
            now()->addMinutes(10),
            fn () => Markdown::parse(file_get_contents($file))
        );
    }
}
