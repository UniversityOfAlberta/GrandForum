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

package com.adobe.flex.extras.controls.springgraph {

	import mx.core.UIComponent;
	import mx.core.Application;
	import mx.controls.Alert;
	import com.adobe.flex.extras.controls.forcelayout.Node;
	import flash.geom.Rectangle;
	
	/** Represents one node of a SpringGraph 
	 * 
	 * @author   Mark Shepherd
	 * @private
	 */
	public class GraphNode extends Node {
		
		public var view: UIComponent;
		public var item: Item;
		
		public override function refresh(): void {
			this.x = getX();
			this.y = getY();
			this.repulsion = getRepulsion();
		}
		
		public override function commit(): void {
			setX(this.x);
			setY(this.y);
		}

		public function GraphNode(view: UIComponent, context: GraphDataProvider, item: Item) {
			super();
			this.view = view;
			this.context = context;
			this.item = item;
		}

		// -------------------------------------------------
		// Private stuff 
		// -------------------------------------------------

	    private function getX(): Number {
	    	return view.x;// + (view.width / 2); // we use the center point
	    }
	    
	    private function setX(x: Number): void {
	    	/*
	    	if(context.boundary != null) {
	    		if((x < context.boundary.left) || ((x + view.width) > context.boundary.right))
	    			return;
	    	}
	    	*/
	    	if((x != (view.x/* + (view.width / 2)*/)) && item.okToMove()) {
		    	context.layoutChanged = true;
		    	view.x = x; //  - (view.width / 2);
		    }
	    }
	    
	    private function getY(): Number {
	    	return view.y;// + (view.height / 2); // we use the center point
	    }
	    
	    private function setY(y: Number): void {
	    	/*
	    	if(context.boundary != null) {
	    		if((y < context.boundary.top) || ((y + view.height) > context.boundary.bottom))
	    			return;
	    	}
	    	*/
	    	if((y != (view.y/* + (view.height / 2)*/)) && item.okToMove()) {
		    	context.layoutChanged = true;
		    	view.y = y; // - (view.height / 2);
		    }
	    }
	    
		private function getRepulsion(): int {
			var result: int = (view.width + view.height) * context.repulsionFactor;
			if(result == 0)
				return context.defaultRepulsion;
			return result;
		}
		
		private var context: GraphDataProvider;
	}
}