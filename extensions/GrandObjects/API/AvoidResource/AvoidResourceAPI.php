<?php

/**
 * Class Avoid Resource
 * API Class for interacting with individual Avoid Resource
 */
class AvoidResourceAPI extends RESTAPI {

  /**
   * doGET handler for get request method
   * @return mixed
   */
    function doGET(){
        if($this->getParam('id') != ""){
            $me = Person::newFromWgUser();
            $avoid_resource = AvoidResource::newFromId($this->getParam('id'));
            if(!$me->isLoggedIn()){
                $this->throwError("You must be logged in to view this sop");
            }
            return $avoid_resource->toJSON();
	}
	else{
            $avoid_resources = new Collection(AvoidResource::getAllAvoidResources());
            return $avoid_resources->toJSON();
        }

    }

  /**
   * doPOST handler for post request method
   * @return bool
   */
    function doPOST(){
        $avoid_resource = new AvoidResource(array());
        header('Content-Type: application/json');
        $avoid_resource->ResourceAgencyNum = $this->POST('ResourceAgencyNum');
        $avoid_resource->Split = $this->POST('Split');
        $avoid_resource->PublicName = $this->POST('PublicName');
        $avoid_resource->Category = $this->POST('Category');
        $avoid_resource->SubCategory = $this->POST('SubCategory');
        $avoid_resource->SubSubCategory = $this->POST('SubSubCategory');
        $avoid_resource->PhysicalAddress1 = $this->POST('PhysicalAddress1');
        $avoid_resource->PhysicalCity = $this->POST('PhysicalCity');
        $avoid_resource->PhysicalCounty = $this->POST('PhysicalCounty');
        $avoid_resource->WebsiteAddress = $this->POST('WebsiteAddress');
        $avoid_resource->AgencyDescription = $this->POST('AgencyDescription');
        $avoid_resource->Eligibility = $this->POST('Eligibility');
        $avoid_resource->TaxonomyTerms = $this->POST('TaxonomyTerms');
        $status = $avoid_resource->create();
        if(!$status){
            $this->throwError("The avoid_resource could not be created");
        }
        $avoid_resource = AvoidResource::newFromId($avoid_resource->getId());
        return $avoid_resource->toJSON();
    }

  /**
   * doPUT handler for put request method
   * @return bool
   */
    function doPUT(){
        $avoid_resource = AvoidResource::newFromId($this->getParam('id'));
        if($avoid_resource == null || $avoid_resource->subject == ""){
            $this->throwError("This avoid resource does not exist");
        }
        header('Content-Type: application/json');
        $avoid_resource->ResourceAgencyNum = $this->POST('ResourceAgencyNum');
        $avoid_resource->Split = $this->POST('Split');
        $avoid_resource->PublicName = $this->POST('PublicName');
        $avoid_resource->Category = $this->POST('Category');
        $avoid_resource->SubCategory = $this->POST('SubCategory');
        $avoid_resource->SubSubCategory = $this->POST('SubSubCategory');
        $avoid_resource->PhysicalAddress1 = $this->POST('PhysicalAddress1');
        $avoid_resource->PhysicalCity = $this->POST('PhysicalCity');
        $avoid_resource->PhysicalCounty = $this->POST('PhysicalCounty');
        $avoid_resource->WebsiteAddress = $this->POST('WebsiteAddress');
        $avoid_resource->AgencyDescription = $this->POST('AgencyDescription');
        $avoid_resource->Eligibility = $this->POST('Eligibility');
        $avoid_resource->TaxonomyTerms = $this->POST('TaxonomyTerms');
        $status = $avoid_resource->update();
        if(!$status){
            $this->throwError("The avoid_resource could not be updated");
        }
        $avoid_resource = AvoidResource::newFromId($this->getParam('id'));
        return $avoid_resource->toJSON();
    }

  /**
   * doDELETE handler for delete request method
   * @return bool
   */
    function doDELETE(){
        return false;
    }
}

?>
