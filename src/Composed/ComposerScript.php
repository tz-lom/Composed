<?php

namespace Composed;

use Composer\Script\Event;

class ComposerScript
{
    public static function rebuildCache(Event $event)
    {
        $cache = CacheManager::instance();
        $event->getIO()->write('Rebuilding class cache (may take long time)');
        $cache->rebuildCache();
    }
}
