<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $currentLocale = $request->getSession()->get('_locale');

        $path = $request->getPathInfo();
        if ($this->startsWith($path, '/fr/') && $currentLocale != 'fr'){
            $currentLocale = 'fr';
        } else if($this->startsWith($path, '/en/') && $currentLocale != 'en'){
            $currentLocale = 'en';
            $request->getSession()->set('_locale', 'en');
        } else if(!$currentLocale){
            $currentLocale = 'en';
            $request->getSession()->set('_locale', $currentLocale);
            $supportedLangs = array('fr', 'fr-FR', 'FR', 'fr-BE');
            $languages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($languages as $lang)
            {
                if(in_array($lang, $supportedLangs))
                {
                    // Set the page locale to the first supported language found
                    $currentLocale = 'fr';
                    $request->getSession()->set('_locale', 'fr');
                    break;
                }
            }
        }

        $request->getSession()->set('_locale', $currentLocale);
        $request->setLocale($currentLocale);

        if(strlen($path) == 0 || $path == '/' || $path == '/fr/' || $path == '/en/'){
            $path = $currentLocale.'/login';
            $request->setLocale($currentLocale);
            $event->setResponse(new RedirectResponse($path));
        } else if(!$this->endsWith($path, 'logout')) {
            if (!$this->startsWith($path, '/fr/') && !$this->startsWith($path, '/en/')) {
                $request->setLocale($currentLocale);
                $event->setResponse(new RedirectResponse('/'.$currentLocale.$request->getPathInfo()));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}