import org.apache.commons.collections15.Transformer;
import edu.uci.ics.jung.graph.SparseMultigraph;

public class EdgeWeightTransformer implements Transformer<Edge, Number> {
	
	private double maxEdgeCount = 0;
	
	public EdgeWeightTransformer(SparseMultigraph<Node, Edge> graph){
		for(Edge e : graph.getEdges()){
			this.maxEdgeCount = Math.max(this.maxEdgeCount, e.getCount());
		}
	}
	
	private double getRawWeight(Edge edge) {
		return Math.ceil((this.maxEdgeCount/(edge.getCount())));
		//return (1d/edge.getCount());
	}
	
	@SuppressWarnings("unused")
	private double getSumOfVertex(Node v){
		double sum = 0;
		for(Edge e : v.getEdges()){
			sum += this.getRawWeight(e);
		}
		return sum;
	}
	
	public Number transform(Edge edge) {
		/*Node source = edge.getSource();
		double sum = this.getSumOfVertex(source);
		return this.getRawWeight(edge)/(sum);*/
		return (int)Math.ceil((this.maxEdgeCount/(edge.getCount())));
	}

}
