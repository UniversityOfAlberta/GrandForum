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
	import mx.core.UIComponent;
	
	/** Defines an object that knows how to create views for Items. */
	public interface IViewFactory
	{
		/** 
		 * Create a UIComponent to represent a given Item in a SpringGraph. The returned UIComponent should
		 * be a unique instance dedicated to that Item. This function might return a unique view component
		 * on each call, or it might cache views and return the same view if called repeatedly 
		 * for the same item. This function may return different classes of object based on the type
		 * or data of the Item.
		 * @param item an item for which y
		 * @return a unique UIComponent to represent the Item. This component must also implement the IDataRenderer interface.
		 * It's OK to return null.
		 * 
		 */
		function getView(item: Item): UIComponent;
	}
}