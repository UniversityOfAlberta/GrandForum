import java.util.LinkedHashMap;
import java.util.Map;

import edu.uci.ics.jung.algorithms.shortestpath.DijkstraDistance;
import edu.uci.ics.jung.algorithms.shortestpath.Distance;
import edu.uci.ics.jung.graph.Graph;

public class DisconnectedDistance implements Distance<Node> {
	
	private DijkstraDistance<Node, Edge> distance;
	private Graph<Node, Edge> graph;
	
	/**
	 * Constructs a new DisconnectedDistance to approximate the distance between disconnected nodes
	 * @param graph The graph to check the distance on
	 */
	public DisconnectedDistance(Graph<Node, Edge> graph){
		this.graph = graph;
		this.distance = new DijkstraDistance<Node, Edge>(this.graph);
	}

	public Number getDistance(Node source, Node target) {
		Double d = (Double) this.distance.getDistance(source, target);
		if(d == null){
			d = Double.MAX_VALUE;
		}
		return d;
	}

	public Map<Node, Number> getDistanceMap(Node v) {
		LinkedHashMap<Node, Number> map = new LinkedHashMap<Node, Number>();
		for(Node key : this.graph.getVertices()){
			Number distance = this.getDistance(v, key);
			if(distance.doubleValue() > 0){
				map.put(key, distance);
			}
		}
		return map;
	}

}
