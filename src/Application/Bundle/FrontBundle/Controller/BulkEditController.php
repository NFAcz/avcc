<?php

namespace Application\Bundle\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\Bundle\FrontBundle\SphinxSearch\SphinxSearch;
use Application\Bundle\FrontBundle\Helper\SphinxHelper;

/**
 * Bulk Edit controller.
 *
 * @Route("/bulkedit")
 */
class BulkEditController extends Controller
{

    /**
     * Make records to display for dataTables.
     *
     * @param Request $request
     *
     * @Route("/validation", name="bulkedit_validation")
     * @Method("POST")
     * @Template("ApplicationFrontBundle:Records:default.html.php")
     * @return json
     */
    public function validation(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $posted = $request->request->all();
            $recordIds = $posted['records'];
            $html = '';
            $errorMsg = '';
            $em = $this->getDoctrine()->getManager();
            if ($recordIds) {
                if ($recordIds == 'all') {
                    $sphinxInfo = $this->getSphinxInfo();
                    $html = "all records";
                } else {
                    $recordIdsArray = explode(',', $recordIds);                    
                    $records = $em->getRepository('ApplicationFrontBundle:Records')->findRecordsByIds($recordIdsArray);
                    $mediaType = $records[0]->getMediaType()->getId();
                    $format = $records[0]->getFormat()->getId();
//                    foreach ($records as $record){
//                        
//                    }
                    $html = "$mediaType  $format <br />";
                }
                $templateParameters = array('selectedrecords' => $recordIds);
                $html .= $this->container->get('templating')->render('ApplicationFrontBundle:BulkEdit:bulkedit.html.php', $templateParameters);
                $success = true;
            } else {
                $success = false;
                $errorMsg = 'Select records to edit.';
            }

            $data['total_count'] = 0;

            echo json_encode(array('success' => $success, 'msg' => $errorMsg, 'html' => $html, 'count' => $data['total_count']));
            $session = $this->getRequest()->getSession();
            $session->remove("saveRecords");
            $session->remove("allRecords");
            exit;
        }
    }

    public function editAction()
    {
        
    }

    /**
     * Get sphinx parameters
     *
     * @return array
     */
    protected function getSphinxInfo()
    {
        return $this->container->getParameter('sphinx_param');
    }

}
