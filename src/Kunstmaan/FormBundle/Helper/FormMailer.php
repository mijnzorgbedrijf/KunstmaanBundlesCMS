<?php

namespace Kunstmaan\FormBundle\Helper;

use Kunstmaan\FormBundle\Entity\FormSubmission;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * The form mailer
 */
class FormMailer implements FormMailerInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var Environment */
    private $twig;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(Swift_Mailer $mailer, Environment $twig, RequestStack $requestStack)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * @param FormSubmission $submission The submission
     * @param string         $from       The from address
     * @param string         $to         The to address(es) seperated by \n
     * @param string         $subject    The subject
     */
    public function sendContactMail(FormSubmission $submission, $from, $to, $subject)
    {
        $request = $this->requestStack->getCurrentRequest();

        $toArr = explode("\r\n", $to);

        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($toArr)
            ->setBody(
                $this->twig->render(
                    '@KunstmaanForm/Mailer/mail.html.twig',
                    [
                        'submission' => $submission,
                        'host' => $request->getScheme() . '://' . $request->getHttpHost(),
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
