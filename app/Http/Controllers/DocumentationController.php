<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Mail\Markdown;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    const README = 'README.md';
    const Installation = 'Installation.md';
    const Configuration = 'Configuration.md';
    const Commands = 'Commands.md';
    const Broadcasting = 'Broadcasting.md';
    const ChatBots = 'ChatBots.md';
    const Calling = 'Calling.md';
    const Composer = 'Composer.md';
    const Helpers = 'Helpers.md';

    /**
     * @return View
     */
    public function index(): View
    {
        return view('docs.portal')->with([
            'title' => 'Documentation',
            'markdown' => $this->parse(self::README),
        ]);
    }

    /**
     * @return View
     */
    public function install(): View
    {
        return view('docs.portal')->with([
            'title' => 'Installation',
            'markdown' => $this->parse(self::Installation),
        ]);
    }

    /**
     * @return View
     */
    public function config(): View
    {
        return view('docs.portal')->with([
            'title' => 'Configurations',
            'markdown' => $this->parse(self::Configuration),
        ]);
    }

    /**
     * @return View
     */
    public function commands(): View
    {
        return view('docs.portal')->with([
            'title' => 'Commands',
            'markdown' => $this->parse(self::Commands),
        ]);
    }

    /**
     * @return View
     */
    public function broadcasting(): View
    {
        return view('docs.portal')->with([
            'title' => 'Broadcasting',
            'markdown' => $this->parse(self::Broadcasting),
        ]);
    }

    /**
     * @return View
     */
    public function bots(): View
    {
        return view('docs.portal')->with([
            'title' => 'Chat Bots',
            'markdown' => $this->parse(self::ChatBots),
        ]);
    }

    /**
     * @return View
     */
    public function calling(): View
    {
        return view('docs.portal')->with([
            'title' => 'Calling',
            'markdown' => $this->parse(self::Calling),
        ]);
    }

    /**
     * @return View
     */
    public function composer(): View
    {
        return view('docs.portal')->with([
            'title' => 'Messenger Composer',
            'markdown' => $this->parse(self::Composer),
        ]);
    }

    /**
     * @return View
     */
    public function helpers(): View
    {
        return view('docs.portal')->with([
            'title' => 'Helpers and Facades',
            'markdown' => $this->parse(self::Helpers),
        ]);
    }

    /**
     * @param string $markdownFile
     * @return HtmlString
     * @throws NotFoundHttpException
     */
    private function parse(string $markdownFile): HtmlString
    {
        $file = storage_path('app/messenger-docs/'.$markdownFile);

        if (! file_exists($file)) {
            throw new NotFoundHttpException("The { $markdownFile } markdown file was not found. Please run the command 'php artisan messenger:docs:download' to download it.");
        }

        return Markdown::parse(file_get_contents($file));
    }
}
