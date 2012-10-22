/*
 Copyright 2006 Mark E Shepherd

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

package
{
	import mx.rpc.http.HTTPService;
	import mx.managers.CursorManager;
	
	/**
	 * Encapsulation of the Amazon Web Service. Contains static functions for performing specific calls on the service.
	 * 
	 * @author Mark Shepherd
	 */
	public class AmazonService
	{
		private static var urlBase: String = "http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=00WQKXFRETRXJMXW8682&";
		
		public static function getItemInfo(id: String, client: Object): void {
			var service: HTTPService = new HTTPService();
			service.resultFormat="e4x";
			service.url = urlBase + "Operation=ItemLookup&ResponseGroup=Reviews,Images,Large&ItemId=" + id;
			service.addEventListener("result", client.getItemInfoResult);
			service.addEventListener("fault", client.getItemInfoFault);
			service.send();
		}
		
		public static function getDetailPageUrl(id: String): String {
			return "http://www.amazon.com/gp/pdp/profile/" + id;
		}
	}
}