import java.util.HashMap;

public class Edge {

	private static HashMap<String, Edge> cache = new HashMap<String, Edge>();
	
	private Node source;
	private Node target;
	private int count;
	
	public static Edge create(Node source, Node target){
		String key = source.toString() + target.toString();
		if(Edge.cache.containsKey(key)){
			Edge e = Edge.cache.get(key);
			e.count++;
			return e;
		}
		Edge e = new Edge(source, target);
		Edge.cache.put(key, e);
		return e;
	}
	
	public static void clearCache(){
		Edge.cache.clear();
	}
	
	public Edge(Node source, Node target){
		this.count = 1;
		this.source = source;
		this.target = target;
		this.source.addEdge(this);
		this.target.addEdge(this);
	}
	
	public int getCount(){
		return this.count;
	}
	
	public Node getSource(){
		return this.source;
	}
	
	public Node getTarget(){
		return this.target;
	}
	
	public String toString(){
		return this.source.toString() + this.target.toString();
	}
	
}
