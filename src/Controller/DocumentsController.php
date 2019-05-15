<?php
// src/Controller/DocumentsController.php
namespace App\Controller;

use App\Entity\Document;
use App\Service\FileUploadService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class DocumentsController extends Controller
{
    /**
     * @Route("/{_locale}/documents", name="documents")
     * @param Request $request
     * @param FileUploadService $fileUploadService
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadAction(Request $request, FileUploadService $fileUploadService, LoggerInterface $logger)
    {
        $this->get('session')->set('currentPage', 'documents');
        $user = $this->getUser();
        $documents = $this->getDoctrine()
            ->getRepository(Document::class)
            ->findBy(array('account' => $user));
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, array('label' => 'New file'))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $newDocument = $fileUploadService->upload($file, $user, $logger);
            if($request->getLocale() == 'fr') {
                $this->addFlash('info', 'Fichier ' . $file->getClientOriginalName() . " envoyé");
            } else {
                $this->addFlash('info', 'File ' . $file->getClientOriginalName() . " uploaded");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newDocument);
            $entityManager->flush();
            $documents[] = $newDocument;
        }
        return $this->render('documents.html.twig', array(
            'currentUser' => $user,
            'form' => $form->createView(),
            'documents' => $documents,
        ));
    }

    /**
     * @Route("{_locale}/documents/{docId}", name="document")
     * @param LoggerInterface $logger
     * @param $docId
     * @return BinaryFileResponse
     */
    public function openDoc(LoggerInterface $logger, $docId)
    {
        $user = $this->getUser();
        $document = $this->getDoctrine()
            ->getRepository(Document::class)
            ->find($docId);
        if($document == null || $document->getAccount()->getId() != $user->getId()){
            $this->addFlash('error', 'Requested document was not found ');
            return null;
        }
        $path = $document->getPath();
        $logger->error('path: '.realpath($path));
        $response = new BinaryFileResponse($path);

        // To generate a file download, you need the mimetype of the file
        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

        // Set the mimetype with the guesser or manually
        if($mimeTypeGuesser->isSupported()){
            // Guess the mimetype of the file according to the extension of the file
            $response->headers->set('Content-Type', $mimeTypeGuesser->guess($path));
        }else{
            // Set the mimetype of the file manually, in this case for a text file is text/plain
            $response->headers->set('Content-Type', 'text/plain');
        }

        // Set content disposition inline of the file
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $document->getOriginalName()
        );

        return $response;
    }
}