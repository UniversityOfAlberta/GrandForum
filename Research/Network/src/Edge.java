import java.util.HashMap;

import edu.uci.ics.jung.graph.util.EdgeType;

public class Edge {

	private static HashMap<String, Edge> cache = new HashMap<String, Edge>();
	
	private Node source;
	private Node target;
	private EdgeType direction;
	private int count;
	
	/**
	 * Creates a new Edge between source and target
	 * @param source The source Node
	 * @param target The target Node
	 * @param direction The type of edge this is to be (Directed/Undirected)
	 * @return The newly created edge
	 */
	public static Edge create(Node source, Node target, EdgeType direction){
		String key = source.toString() + target.toString();
		if(Edge.cache.containsKey(key) && Edge.cache.get(key).getDirection().equals(direction)){
			Edge e = Edge.cache.get(key);
			e.count++;
			return e;
		}
		Edge e = new Edge(source, target, direction);
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
	 * @param direction The type of edge this is to be (Directed/Undirected)
	 */
	public Edge(Node source, Node target, EdgeType direction){
		this.count = 1;
		this.source = source;
		this.target = target;
		this.direction = direction;
		this.source.addEdge(this);
		if(this.direction.equals(EdgeType.UNDIRECTED)){
			// Only add the edge to the target if it is undirected
			this.target.addEdge(this);
		}
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
	
	/**
	 * Returns the direction of the Edge
	 * @return The direction of the Edge
	 */
	public EdgeType getDirection(){
		return this.direction;
	}
	
	public String toString(){
		return this.source.toString() + this.target.toString();
	}
	
}
