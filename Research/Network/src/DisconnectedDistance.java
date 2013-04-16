import java.util.LinkedHashMap;
import java.util.Map;

import edu.uci.ics.jung.algorithms.shortestpath.DijkstraDistance;
import edu.uci.ics.jung.algorithms.shortestpath.Distance;
import edu.uci.ics.jung.graph.Graph;

public class DisconnectedDistance implements Distance<Node> {

	private static Double maxDist = Double.MIN_VALUE;
	
	private DijkstraDistance<Node, Edge> distance;
	private Double maxDistance;
	private Graph<Node, Edge> graph;
	
	public DisconnectedDistance(Graph<Node, Edge> graph){
		this.graph = graph;
		this.distance = new DijkstraDistance<Node, Edge>(this.graph);
		this.maxDistance = this.determineMaxDistance();
		if(DisconnectedDistance.maxDist == Double.MIN_VALUE){
			DisconnectedDistance.maxDist = this.maxDistance;
		}
	}
	
	private Double determineMaxDistance(){
		Double d = 0d;
		for(Node source : this.graph.getVertices()){
			for(Node target : this.graph.getVertices()){
				Double dist = (Double) this.distance.getDistance(source, target);
				if(dist != null){
					d = Math.max(d, dist);
				}
			}
		}
		return d;
	}

	public Number getDistance(Node source, Node target) {
		Double d = (Double) this.distance.getDistance(source, target);
		if(d == null){
			d = this.maxDistance;
		}
		d = (d/this.maxDistance)*DisconnectedDistance.maxDist;
		return d;
	}

	public Map<Node, Number> getDistanceMap(Node v) {
		LinkedHashMap<Node, Number> map = new LinkedHashMap<Node, Number>();
		for(Node key : this.graph.getVertices()){
			map.put(key, this.getDistance(v, key));
		}
		return map;
	}

}
