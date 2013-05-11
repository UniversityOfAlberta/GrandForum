import java.util.HashMap;

import org.apache.commons.collections15.Transformer;

import edu.uci.ics.jung.graph.Graph;

public class EdgeWeightTransformer implements Transformer<Edge, Number> {
	
	private HashMap<Edge, Double> rawEdgeWeights;
	private boolean inverted = true;
	
	public EdgeWeightTransformer(Graph<Node, Edge> graph, boolean inverted){
		this.rawEdgeWeights = new HashMap<Edge, Double>();
		for(Node n : graph.getVertices()){
			for(Edge e : n.getSourceEdges()){
				this.rawEdgeWeights.put(e, this.getRawWeight(e));
			}
		}
		this.inverted = inverted;
	}
	
	public Double getRawWeight(Edge edge){
		Node n = edge.getSource();
		double nSources = 0;
		for(Edge e : n.getSourceEdges()){
			nSources += e.getCount();
		}
		double nEdges = (double)nSources;
		if(this.inverted){
			return Math.log10(1 + (double)nEdges/(double)edge.getCount());
		}
		else{
			return (double)edge.getCount()/(double)nEdges;
		}
	}
	
	public Number transform(Edge edge){
		if(this.inverted){
			double sum = 0.0;
			Node n = edge.getSource();
			for(Edge e : n.getSourceEdges()){
				sum += this.rawEdgeWeights.get(e).doubleValue();
			}
			double weight = this.rawEdgeWeights.get(edge)/sum;
			return weight;
		}
		else{
			return this.rawEdgeWeights.get(edge);
		}
	}

}
