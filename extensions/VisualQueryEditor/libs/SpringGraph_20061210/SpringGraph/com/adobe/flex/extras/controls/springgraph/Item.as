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


package com.adobe.flex.extras.controls.springgraph
{
	/** The base class for all Graph items.
	 * @author   Mark Shepherd
	 * 
	 */
	public class Item
	{
	    /**
	     *  Constructor for Item. 
	     * 
	     * @param id if non-null, this becomes the unique id for this item. WARNING: every item
	     * must have an id that is different from the id of all other items. If you do not provide
	     * an id, we create one for you.
	     */
		public function Item(id: String = null) {
			if(id != null)
				_id = id;
			else
				_id = "$$__item" + ++counter;
		}
		
	    /**
	     *  This item's unique id. Every item has a unique id.
	     */
	    [Bindable]
		public function get id(): String {
			return _id;
		}
		public function set id(s: String): void {
		}
	
		private var _id: String;
		
	    /**
	     *  This item's data, if any.
	     */
	    [Bindable]
		public function get data(): Object {
			return _data;
		}
		public function set data(o: Object): void {
			_data = o;
		}
	
		private var _data: Object;
		
	    /**
	     *  Find out if it's ok for the user to move this item with the mouse.
	     *
	     *  @return true if it is currently permitted for the user to move this item. By default, this is true,
	     *  but subclasses of Item can override this as desired.
	     */
		public function okToMove(): Boolean {
			return true;
		}
		
		private static var counter: int = 0;
	}
}