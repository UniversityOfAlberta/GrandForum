import java.util.HashMap;
import java.util.Vector;

import edu.uci.ics.jung.graph.SparseMultigraph;

public abstract class Centrality extends Thread {

	protected SparseMultigraph<Node, Edge> graph;
	protected HashMap<String, Double> results;
	
	public Centrality(SparseMultigraph<Node, Edge> graph){
		this.graph = graph;
		this.results = new HashMap<String, Double>();
	}
	
	public HashMap<String, Double> getResults(){
		return this.results;
	}
	
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
