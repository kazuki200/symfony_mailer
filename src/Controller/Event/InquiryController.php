<?php

namespace App\Controller\Event;

use App\Entity\Event\Inquiry;
use App\Form\InquiryType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class InquiryController extends AbstractController
{
    #[Route('/event/inquiry', name: 'app_event_inquiry')]
    public function index(): Response
    {
        $inquiry = new Inquiry();
        $form = $this->createForm(InquiryType::class, $inquiry);


        return $this->render('event/inquiry/index.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/event/inquiry/confirm', name: 'app_event_inquiry_confirm', methods: ['POST'])]
    public function confirm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, ParameterBagInterface $bag): Response
    {
        $inquiry = new Inquiry();

        $form = $this->createForm(InquiryType::class, $inquiry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->getMethod() === 'POST') {
            $data = $form->getData();
            try {
                $email = (new TemplatedEmail())
                    ->from('from@email.com')
                    ->to('to@email.com')
                    ->subject('テストだよ')
                    ->htmlTemplate('mail/text.html.twig')
                    ->context([
                        'form' => $form->createView(),
                    ]);

                $mailer->send($email);

                // Uncomment the following lines to save the inquiry to the database
                 $entityManager->persist($inquiry);
                 $entityManager->flush();
                return $this->redirectToRoute('app_event_inquiry');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'メールの送信中にエラーが発生しました。');
            }

            return $this->render('event/inquiry/confirm.html.twig', [
                'ConfirmForm' => $form->createView(),
                'data' => $data,
            ]);
        }
        return $this->redirectToRoute('app_event_inquiry');
    }
}
