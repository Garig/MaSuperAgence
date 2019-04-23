<?php

namespace App\Listener;

use App\Entity\Picture;
use Doctrine\Common\EventSubscriber;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ImageCacheSubscriber implements EventSubscriber{
    
    private $cacheManager;
    
    private $uploaderHelper;

    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper){
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }
    
    public function getSubscribedEvents()
    {
        return [
            'preRemove',
            'preUpdate'
        ];
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Picture) {
            return;
        }
        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }
    
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$args->getEntity() instanceof Picture) {
            return;
        }
        if ($args->getEntity()->getImageFile() instanceof UploadedFile) {
            $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
        }
    }
}