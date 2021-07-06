/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class("Settings_ControlLayoutFields_Edit1_Js",{},{

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step1Container;
	},

	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step1Container = element;
		return this;
	},

	/**
	 * Function  to intialize the reports step1
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#workflow_step1');
		}
		if(container.is('#workflow_step1')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#workflow_step1'));
		}
	},

	submit : function(){
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		app.helper.showProgress();
		app.request.post({data:formData}).then(
			function(err,data){
				if(err === null) {
					form.hide();
					app.helper.hideProgress();
					aDeferred.resolve(data);
				}else{
					// to do
				}
			}
		);
		return aDeferred.promise();
	},
	
	registerEvents : function(){
		var container = this.getContainer();
		container.find('[type="submit"]').removeAttr('disabled');

	}
});
