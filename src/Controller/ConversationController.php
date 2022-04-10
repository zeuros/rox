<?php

namespace App\Controller;

use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Model\ConversationModel;
use App\Utilities\ConversationThread;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This controller handles all requests regarding single conversations (messages, hosting requests and invitations) like
 * viewing, replying, etc.
 */
class ConversationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    protected ConversationModel $conversationModel;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ConversationModel $conversationModel,
        EntityManagerInterface $entityManager
    ) {
        $this->conversationModel = $conversationModel;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/conversation/{id}", name="conversation_view",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_VIEW", subject="message")
     */
    public function viewConversation(Message $message): Response
    {
        $template = $this->getViewTemplate($message);

        return $this->viewThread($message, $template, false);
    }

    /**
     * @Route("/conversation/{id}/deleted", name="conversation_view_with_deleted",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_VIEW", subject="message")
     */
    public function viewConversationWithDeletedMessages(Message $message): Response
    {
        $template = $this->getViewTemplate($message);

        return $this->viewThread($message, $template, true);
    }

    /**
     * @Route("/conversation/{id}/reply", name="conversation_reply",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_REPLY", subject="message")
     */
    public function reply(Message $message): Response
    {
        // Always reply to the last item in the thread
        $conversationThread = new ConversationThread($this->entityManager);
        $thread = $conversationThread->getThread($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            // In case someone replies at the same time as the other side a redirect would kill the form.
            // Just set the last message and continue with the process.
            $message = $current;
        }

        // Check if member is part of this conversation
        /** @var Member $member */
        $member = $this->getUser();
        if ($member !== $message->getSender() && $member !== $message->getReceiver()) {
            return $this->redirectToRoute('conversations', ['conversationsType' => 'conversations']);
        }
        $controllerAndMethod = $this->getControllerAndMethod($message, 'reply');

        return $this->forward($controllerAndMethod, [
            'message' => $message,
        ]);
    }

    /**
     * @Route("/conversation/{id}/delete", name="conversation_delete",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_VIEW", subject="message")
     */
    public function deleteConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationDeleted($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.deleted');

        return $this->redirectToRoute('conversations_deleted');
    }

    /**
     * @Route("/conversation/{id}/purge", name="conversation_purge",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_VIEW", subject="message")
     */
    public function purgeConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationPurged($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.purged');

        return $this->redirectToRoute('conversations_deleted');
    }

    /**
     * @Route("/conversation/{id}/recover", name="conversation_recover",
     *     requirements={"id": "\d+"}
     * )
     *
     * @IsGranted("CONVERSATION_VIEW", subject="message")
     */
    public function recoverConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->unmarkConversationDeleted($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.recovered');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    /**
     * @Route("/conversation/{id}/spam", name="conversation_mark_spam")
     */
    public function markAsSpam(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationAsSpam($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.spam');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    /**
     * @Route("/conversation/{id}/nospam", name="conversation_mark_nospam")
     */
    public function unmarkAsSpam(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->unmarkConversationAsSpam($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.nospam');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    private function getControllerAndMethod(Message $message, string $method): string
    {
        $controller = '';
        if ($message->isMessage()) {
            $controller = MessageController::class;
        } elseif ($message->isHostingRequest()) {
            $controller = RequestController::class;
        } elseif ($message->isInvitation()) {
            $controller = InvitationController::class;
        }

        return $controller . '::' . $method;
    }

    private function getViewTemplate(Message $message): string
    {
        $template = '';
        if ($message->isMessage()) {
            $template = 'message';
        } elseif ($message->isHostingRequest()) {
            $template = 'request';
        } elseif ($message->isInvitation()) {
            $template = 'invitation';
        }

        return $template . '/view.html.twig';
    }

    private function viewThread(Message $message, string $template, bool $includeDeleted): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $conversationThread = new ConversationThread($this->entityManager);
        $thread = $conversationThread->getThread($message);
        $current = $thread[0];

        $route = 'conversation_view';
        if ($message->getId() !== $current->getId()) {
            if ($includeDeleted) {
                $route .= '_with_deleted';
            }

            return $this->redirectToRoute($route, ['id' => $current->getId()]);
        }

        // Now we're at the latest message in the thread. Check that not all items are deleted/purged depending on the
        // $showDeleted setting
        $spam = $this->checkConversationIsSpam($thread, $member);
        $nothingVisible = $this->checkConversationIsAllDeleted($thread, $member, $includeDeleted);
        if ($nothingVisible) {
            return $this->redirectToRoute('conversations', ['conversationsType' => 'conversations']);
        }

        $this->conversationModel->markConversationAsRead($member, $thread);

        return $this->render($template, [
            'show_deleted' => $includeDeleted,
            'is_spam' => $spam,
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    private function checkConversationIsAllDeleted(array $thread, Member $member, bool $includeDeleted): bool
    {
        $nothingVisible = true;
        foreach ($thread as $threadMessage) {
            if ($includeDeleted) {
                $nothingVisible = $nothingVisible && $threadMessage->isPurgedByMember($member);
            } else {
                $nothingVisible = $nothingVisible && (
                        $threadMessage->isPurgedByMember($member) || $threadMessage->isDeletedByMember($member)
                    );
            }
        }

        return $nothingVisible;
    }

    private function checkConversationIsSpam(array $thread, Member $member): bool
    {
        $spam = false;
        foreach ($thread as $threadMessage) {
            if ($threadMessage->getReceiver() === $member) {
                $spam = $spam || (false !== strpos($threadMessage->getSpamInfo(), SpamInfoType::MEMBER_SAYS_SPAM));
            }
        }

        return $spam;
    }
}
