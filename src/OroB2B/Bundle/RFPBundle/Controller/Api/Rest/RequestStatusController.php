<?php

namespace OroB2B\Bundle\RFPBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @NamePrefix("orob2b_api_rfp_request_status_")
 */
class RequestStatusController extends RestController
{
    /**
     * Rest delete
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete RequestStatus",
     *      resource=true
     * )
     * @Acl(
     *      id="orob2b_rfp_request_status_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroB2BRFPBundle:RequestStatus"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $requesStatus = $em->getRepository('OroB2BRFPBundle:RequestStatus')->find($id);

        if (null === $requesStatus) {
            return new JsonResponse(
                $this->get('translator')->trans('Item not found'),
                Codes::HTTP_NOT_FOUND
            );
        }

        $requesStatus->setDeleted(true);
        $em->flush();

        $result = new Response(null, Codes::HTTP_NO_CONTENT);

        return $result;
    }

    /**
     * @param $id
     *
     * @ApiDoc(
     *      description="Restore RequestStatus",
     *      resource=true
     * )
     * @AclAncestor("orob2b_rfp_request_status_delete")
     *
     * @return Response
     */
    public function restoreAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $requesStatus = $em->getRepository('OroB2BRFPBundle:RequestStatus')->find($id);

        if (null === $requesStatus) {
            return new JsonResponse(
                $this->get('translator')->trans('orob2b.rfp.message.request_status_not_found'),
                Codes::HTTP_NOT_FOUND
            );
        }

        $requesStatus->setDeleted(false);
        $em->flush();

        return $this->handleView(
            $this->view(
                array(
                    'successful' => true,
                    'message' => $this->get('translator')->trans('orob2b.rfp.message.request_status_restored')
                ),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        throw new \LogicException('This method should not be called');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \LogicException('This method should not be called');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \LogicException('This method should not be called');
    }
}
