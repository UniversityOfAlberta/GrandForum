import java.util.HashMap;
import java.util.Vector;

import edu.uci.ics.jung.graph.SparseMultigraph;

public abstract class Centrality extends Thread {

	public static final boolean NORMALIZE = true;
	
	protected SparseMultigraph<Node, Edge> graph;
	protected HashMap<String, Double> results;
	
	/**
	 * Constructs a new Centrality for the specified SparseMultigraph
	 * @param graph the SparseMultigraph which will be used to compute the centrality
	 */
	public Centrality(SparseMultigraph<Node, Edge> graph){
		this.graph = graph;
		this.results = new HashMap<String, Double>();
	}
	
	/**
	 * Returns a HashMap of the Node centralities
	 * @return a HashMap of the Node centralities
	 */
	public HashMap<String, Double> getResults(){
		return this.results;
	}
	
	/**
	 * Returns a Vector containing the names of the nodes in order of rank from highest to lowest
	 * @return a Vector containing the names of the nodes in order of rank from highest to lowest
	 */
	public Vector<String> getRanks(){
		Vector<String> ranks = new Vector<String>();
		for(String key : this.results.keySet()){
			Double score = this.results.get(key);
			int i = 0;
			int sizeBefore = ranks.size();
			for(String k : ranks){
				if(this.results.get(k) <= score){
					ranks.insertElementAt(key, i);
					break;
				}
				i++;
			}
			if(sizeBefore == ranks.size()){
				ranks.add(key);
			}
		}
		return ranks;
	}
	
}
