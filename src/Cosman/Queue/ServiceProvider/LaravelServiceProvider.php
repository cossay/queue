<?php
declare(strict_types = 1);
namespace Cosman\Queue\ServiceProvider;

use Illuminate\Support\ServiceProvider;

/**
 * Laravel service
 *
 * @author cosman
 *        
 */
class LaravelServiceProvider extends ServiceProvider
{
    
    
    public function register()
    {
        
    }

    /**
     *
     * {@inheritdoc}
     * @see \Illuminate\Support\ServiceProvider::provides()
     */
    public function provides()
    {
        return [];
    }
}
