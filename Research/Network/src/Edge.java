import java.util.HashMap;

public class Edge {

	private static HashMap<String, Edge> cache = new HashMap<String, Edge>();
	
	private Node source;
	private Node target;
	private int count;
	
	/**
	 * Creates a new Edge between source and target
	 * @param source The source Node
	 * @param target The target Node
	 * @return The newly created edge
	 */
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
	
	/**
	 * Clears the cache of Edges
	 */
	public static void clearCache(){
		Edge.cache.clear();
	}
	
	/**
	 * Edge constructor: Constructs a new Edge between source and target
	 * @param source The source Node
	 * @param target The target Node
	 */
	public Edge(Node source, Node target){
		this.count = 1;
		this.source = source;
		this.target = target;
		this.source.addEdge(this);
		this.target.addEdge(this);
	}
	
	/**
	 * Returns the number of times this edge occurs
	 * @return The number of times this edge occurs
	 */
	public int getCount(){
		return this.count;
	}
	
	/**
	 * Returns the source Node
	 * @return The source Node
	 */
	public Node getSource(){
		return this.source;
	}
	
	/**
	 * Returns the target Node
	 * @return The target Node
	 */
	public Node getTarget(){
		return this.target;
	}
	
	public String toString(){
		return this.source.toString() + this.target.toString();
	}
	
}
