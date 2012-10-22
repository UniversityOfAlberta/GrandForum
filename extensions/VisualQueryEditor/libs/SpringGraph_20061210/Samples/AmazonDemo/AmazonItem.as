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
	import mx.controls.Alert;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.core.Application;
	import com.adobe.flex.extras.controls.springgraph.Item;

	/**
	 * Represents a single Amazon item. When created, it uses the amazon web service
	 * to find out its title, icon, and similar products.
	 * 
	 * @author Mark Shepherd
	 */
	public class AmazonItem extends Item
	{
		[Bindable]
		public var name: String;
		
		[Bindable]
		public var imageUrl: String;
				
		private var similarProducts: XMLList;
		private var createSimilarsASAP: Boolean = false;
		
		public function AmazonItem(itemId: String, name: String) {
			super(itemId);
			this.name = name;
			AmazonService.getItemInfo(itemId, this);
		}
		
		public function getItemInfoResult(event:ResultEvent):void {
			var info: XML = XML(event.result);
			var ns:Namespace = info.namespace("");
			this.name = info.ns::Items.ns::Item.ns::ItemAttributes.ns::Title;
			this.imageUrl = info.ns::Items.ns::Item.ns::SmallImage.ns::URL;
			similarProducts = info.ns::Items.ns::Item.ns::SimilarProducts.ns::SimilarProduct;
			if(createSimilarsASAP)
				createSimilars();
		}

		public function getItemInfoFault(event:FaultEvent):void {
			Alert.show("getItemInfoFault " + event.toString());
		}
		
		public function createSimilars(): void {
			if(similarProducts == null) {
				createSimilarsASAP = true;
				return;
			}
			var app: AmazonDemo = AmazonDemo(Application.application);
			app.createItems(similarProducts, this);
			similarProducts = null;
		}
	}
}