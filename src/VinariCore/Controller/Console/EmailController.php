<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Controller\Console;

use VinariCore\Mvc\Controller\AbstractActionController;
use RuntimeException;
use Zend\Console\Request as ConsoleRequest;
use Zend\View\Model\ViewModel;
use Zend\Mail\Message;
use Zend\Mail\Transport\Factory as TransportFactory;

class EmailController extends AbstractActionController
{

    public function sendAction()
    {

        $output = [];
        // So hacky, but it works
        exec('ps aux | grep \'[p]\'hp | grep \'[s\']end-emails', $output);
        if (count($output) > 1) {
            print("Already running, exitting\n");
            return;
        }

        print("\n" . self::COLOUR_BOLDWHITE . 'E-mail processor' . self::COLOUR_RESET . "\n");
        print(self::COLOUR_BOLDBLUE . '================' . self::COLOUR_RESET . "\n\n");

        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        try {
            $serviceLocator = $this->getServiceLocator();
            $config = $serviceLocator->get('Config');

            $transport = TransportFactory::create($config['vinari-core']['email']['transport']);

            $objectManager  = $this->getServiceLocator()->get('Doctrine\\ORM\\EntityManager');
            $emailRepository = $objectManager->getRepository($config['vinari-core']['entity']);

            // Check flags
            $limit  = (int)($request->getParam('limit') || $request->getParam('l'));
            $dryRun = (bool)($request->getParam('dry-run'));

            print(self::COLOUR_YELLOW . 'Fetching e-mails to send… ' . self::COLOUR_RESET);
            $emails = $emailRepository->findBy(['status' => 'not-sent']);
            print(self::COLOUR_BOLDGREEN . 'Done.' . self::COLOUR_RESET . "\n");

            if (!count($emails)) {
                print(self::COLOUR_YELLOW . 'Nothing to send; exiting.' . self::COLOUR_RESET . "\n\n");
            } else {
                print(self::COLOUR_YELLOW . 'Found ' . count($emails) . ' e-mails to send.' . self::COLOUR_RESET . "\n");

                print(self::COLOUR_YELLOW . 'Sending e-mails…' . self::COLOUR_RESET . "\n");
                $i = 0;
                foreach ($emails as $email) {
                    print(self::COLOUR_YELLOW . '    ' . $email->getToName() . ' <' . $email->getToAddress() . '>… ' . self::COLOUR_RESET);

                    $mail = new Message();
                    $mail->addTo($email->getToAddresses(), $email->getToName());
                    $mail->addFrom($email->getFromAddress(), $email->getFromName());
                    $mail->setEncoding('UTF-8');
                    $mail->setBody($email->getTextContent());
                    $mail->setSubject($email->getSubject());

                    try {
                        if (!$dryRun) {
                            $transport->send($mail);
                        }

                        $email->setStatus('sent');
                        print(self::COLOUR_BOLDGREEN . 'Done.' . self::COLOUR_RESET . "\n");
                    } catch (\Exception $ee) {
                        $error = $this->logError(($ee->getCode() ? $ee->getCode() : 500), $ee->getMessage(), $ee->getFile(), $ee->getLine(), $ee->getTrace());

                        $email->setError($error);
                        $email->setStatus('failed');
                        print(self::COLOUR_BOLDRED . 'Failed: ' . self::COLOUR_WHITE . $ee->getMessage() . self::COLOUR_RESET . "\n");
                    }

                    if (!$dryRun) {
                        $objectManager->persist($email);
                        $objectManager->flush();
                    }

                    if ($limit > 0 && ++$i >= $limit) {
                        break;
                    }
                }
            }

            print(self::COLOUR_BOLDGREEN . 'Done.' . self::COLOUR_RESET . "\n\n");
        } catch (\Exception $e) {
            print(self::COLOUR_BOLDRED . 'Failed: ' . self::COLOUR_WHITE . $e->getMessage() . self::COLOUR_RESET . "\n\n");
        }

    }

}
