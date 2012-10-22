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
	import flash.display.Graphics;
	import com.adobe.flex.extras.controls.springgraph.Item;
	import mx.core.UIComponent;
	
	/** Defines an object that knows how to draw the edges between 2 items in 
	 * a SpringGraph. */
	public interface IEdgeRenderer
	{
		/** SpringGraph will call this function each time it needs to draw
		 * a link connecting two itemRenderer.
		 * Note that fromView.data is the 'from' Item and toView.data is the 'to' Item.
		 * @param g a Flash graphics object, representing the entire screen area of the 
		 * SpringGraph component. You can use various Flash drawing commands to draw
		 * onto this drawing surface
		 * @param fromView the itemRenderer instance for the 'from' Item of this linik
		 * @param toView the itemRenderer instance for the 'to' Item of this link
		 * @param fromX the x-coordinate of fromView
		 * @param fromY the y-coordinate of fromView
		 * @param toX the x-coordinate of toView
		 * @param toY the y-coordinate of toView
		 * @param graph the Graph that we are drawing
		 * @return true if we successfully drew the edge, false if we want the SpringGraph
		 * to draw the edge. 
		 */
		function draw(g: Graphics, fromView: UIComponent, toView: UIComponent,
			fromX: int, fromY: int, toX: int, toY: int, graph: Graph): Boolean;
	}
}