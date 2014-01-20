import java.util.HashMap;
import java.util.LinkedHashMap;

import edu.uci.ics.jung.graph.SparseMultigraph;

public class Closeness extends Centrality {

	private static HashMap<String, Double> max = new HashMap<String, Double>();
	private static HashMap<String, Double> min = new HashMap<String, Double>();
	private static boolean firstTime = true;
	
	public Closeness(SparseMultigraph<Node, Edge> graph){
		super(graph);
	}
	
	public void run(){
		DisconnectedDistance distance = new DisconnectedDistance(this.graph);
		HashMap<String, Double> max = new HashMap<String, Double>();
		HashMap<String, Double> min = new HashMap<String, Double>();
		
		// Calculating Closeness 
		// (http://toreopsahl.com/2010/03/20/closeness-centrality-in-networks-with-disconnected-components/)
		HashMap<Node, Double> scores = new HashMap<Node, Double>();
		for(Node s : this.graph.getVertices()){
			double sum = 0;
			LinkedHashMap<Node, Number> dMap = (LinkedHashMap<Node, Number>) distance.getDistanceMap(s);
			for(Node t : this.graph.getVertices()){
				if(s != t){
					sum += (1/dMap.get(t).doubleValue());
				}
			}
			scores.put(s, sum/(this.graph.getVertexCount()-1));
		}
		
		for(Node v : this.graph.getVertices()){
			double score = Math.log(1+scores.get(v));
			this.results.put(v.toString(), score);
			if(!max.containsKey(v.getType())){
				max.put(v.getType(), score);
				min.put(v.getType(), 0.0);
			}
			else{
				max.put(v.getType(), Math.max(max.get(v.getType()), score));
			}
			if(Closeness.firstTime || !Centrality.NORMALIZE){
				Closeness.max.put(v.getType(), max.get(v.getType()));
				Closeness.min.put(v.getType(), min.get(v.getType()));
			}
		}
		
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Closeness.min.get(v.getType()))/(Closeness.max.get(v.getType()) - Closeness.min.get(v.getType()));
			this.results.put(v.toString(), Math.max(0, score));
		}
		Closeness.firstTime = false;
	}
	
}
