<?php

namespace Application\Bundle\FrontBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Application\Bundle\FrontBundle\Helper\EmailHelper;
use Application\Bundle\FrontBundle\Entity\UserSettings;

class BackupCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('avcc:backup-report')
                ->setDescription('backup of records')
//                ->addArgument('userId', InputArgument::REQUIRED, 'user id required')
//                ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $entity = $em->getRepository('ApplicationFrontBundle:UserSettings')->findBy(array('enableBackup' => 1));
        if ($entity) {
            foreach ($entity as $record) {
                $var = $record->getBackupEmail();
                $email_to = $this->get_email_to($var);
                $records = $em->getRepository('ApplicationFrontBundle:Records')->findOrganizationRecords($record->getUser()->getOrganizations()->getId());
                if ($records) {
                    $phpExcelObject = $export->generateReport($records);
                    $completePath = $export->saveReport('csv', $phpExcelObject);
                    $text = $completePath;
                }
                //   if ($completePath) {
               // $baseUrl = $this->getContainer()->getParameter('baseUrl');
               // $templateParameters = array('user' => $record->getUser(), 'baseUrl' => $baseUrl, 'fileUrl' => $completePath);
               // $rendered = $this->getContainer()->get('templating')->render('ApplicationFrontBundle:Records:export.email.html.twig', $templateParameters);
                $email = new EmailHelper($this->getContainer());
             //   $subject = 'Record Backup';
                foreach ($email_to as $email_id) {
                    //  $email->sendEmail($rendered, $subject, $this->getContainer()->getParameter('from_email'), $email_id);
                    $email->sendEmail('yahoo', 'just mail', $this->getContainer()->getParameter('from_email'), $email_id);
                }
                $text = 'wowow';
                //      $text = $rendered;
//                } else {
//                    $text = 'record not found';
//                }
            }
        } else {
            $text = 'Hello';
        }
        $output->writeln($text);
    }

    public function get_email_to($array) {
        // $var = $record->getBackupEmail();
        $return = array();
        if (empty($array)) {
            $return = $record->getUser()->getEmail();
        } else {
            $return = explode(',', $array);
        }
        return $return;
    }

}
