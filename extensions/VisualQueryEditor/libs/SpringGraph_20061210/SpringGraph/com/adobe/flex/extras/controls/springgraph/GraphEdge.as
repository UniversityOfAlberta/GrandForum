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
	import com.adobe.flex.extras.controls.forcelayout.AbstractEdge;
	
	/** Represents one edge of a SpringGraph 
	 * 
	 * @author   Mark Shepherd
	 * @private
	 */
	public class GraphEdge extends AbstractEdge
	{
		public static var traversedMap: Object = new Object();
		
		public function get traversed(): Boolean {
			var fromId: String = GraphNode(getFrom()).item.id;
			var toId: String = GraphNode(getTo()).item.id;
			var key: String = fromId + "--" + toId;
			var result: Boolean = traversedMap.hasOwnProperty(key);
			if(result)
				result = result;
			return result;
		}
		
		public function GraphEdge(f: GraphNode, t: GraphNode, len: int) {
			super(f, t, len);
		}
		
	    public override function getLength(): int {
	    	var result: int = (GraphNode(to).view.width + GraphNode(to).view.height +
	       		GraphNode(from).view.width + GraphNode(from).view.height) / 4;
	       	if(result > 0)
	       		return result;
	       	else
	       		return 50; // !!@
	    }
	}
}