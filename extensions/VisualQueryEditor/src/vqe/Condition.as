package vqe
{
	public class Condition
	{
		private var property: Property;
		private var operator: String;
		private var value: String;
		private var actorNameValue: String;
		private var propNameValue: String;
		private var idNumber: int;
		
		public function Condition(property:Property, operator:String, idNumber:int)
		{
			this.property = property;
			this.operator = operator;
			this.idNumber = idNumber;
		}
		
		public function setConstantValue(value:String) : void {
			this.value = value;
			this.actorNameValue = "";
			this.propNameValue = "";
		}
		
		public function setPropertyValue(a:String, p:String) : void {
			this.actorNameValue = a;
			this.propNameValue = p;
			this.value = "";
		}
		
		public function getProperty() : Property
		{
			return property;
		}
		
		public function getOperator() : String
		{
			return operator;
		}
		
		public function getValue() : String
		{
			return value;
		}
		
		public function getIdNumber() : int
		{
			return idNumber;
		}
		
		public function getActorNameValue() : String
		{
			return actorNameValue;
		}
		
		public function getPropNameValue() : String
		{
			return propNameValue;
		}
	}
}