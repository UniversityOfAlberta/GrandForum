package vqe
{
	/**
	 * the NodeItem class Represents a single Node item in the graph. 
	 * 
	 * @author Diego Fernando Serrano
	 */
	import com.adobe.flex.extras.controls.springgraph.Item;

	public class NodeItem extends Item
	{
		private var name: String;
		private var type: String = "";
		private var idDb: int;
		
		/**
		 * Constructs a new NodeItem object.
		 * 
		 * @param	itemId	Id of the node.
		 * @param	name	Name for the node.
		 * @param	type	Type of the node (actor, relation,...).
		 */ 
		public function NodeItem(itemId: String, name: String, type: String) {
			super(itemId);
			this.name = name;
			this.type = type;
		}
		
		
		/**
		 * Sets the value for the type.
		 * 
		 * @param	type	Type of the node.
		 */
		public function setType(type: String):void {
			this.type = type;
		}
		
		
		/**
		 * Sets the value for the id of the database.
		 * 
		 * @param	idDb	Id.
		 */
		public function setIdDb(idDb: int):void {
			this.idDb = idDb;
		}
		
		
		/**
		 * Gets the id.
		 * 
		 * @return	Id.
		 */
		public function getIdDb():int {
			return idDb;
		}
		
		
		/**
		 * Gets the id.
		 * 
		 * @return	Id of the node.
		 */
		public function getId():String {
			return id;
		}
		
		/**
		 * Gets the name.
		 * 
		 * @return	Name of the node.
		 */
		public function getName():String {
			return name;
		}
		
		/**
		 * Sets the name.
		 * 
		 * @param	name	Name of the node.
		 */
		public function setName(name:String):void {
			this.name = name;
		}
		
		/**
		 * Gets the type.
		 * 
		 * @return	Type of the node.
		 */
		public function getType():String {
			return type;
		}
		
		/**
		 * Gets the color depending on the type.
		 * 
		 * @return 	Node color.
		 */
		public function getColor():String {
			var color:String;
			
			switch (this.type) {
				case "actor":
								color = '0x8ECD3A'; break;
				case "relation":
								color = '0x99FF00'; break;
				case "property":
								color = '0x3366CC'; break;
				case "condition":
								color = '0x33CCFF'; break;
			}
			
			return color;
		}
		
		
		/**
		 * Gets the font sizedepending on the type.
		 * 
		 * @return 	Font size.
		 */
		public function getFontSize():int {
			var size:int;
			
			switch (this.type) {
				case "actor":
								size = 14; break;
				case "relation":
								size = 12; break;
				case "property":
								size = 10; break;
				case "condition":
								size = 10; break;
			}
			
			return size;
		}
	}
}