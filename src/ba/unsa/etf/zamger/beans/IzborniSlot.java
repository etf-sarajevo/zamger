package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * IzborniSlot generated by hbm2java
 */
public class IzborniSlot implements java.io.Serializable {

	private IzborniSlotId id;
	private Predmet predmet;

	public IzborniSlot() {
	}

	public IzborniSlot(IzborniSlotId id, Predmet predmet) {
		this.id = id;
		this.predmet = predmet;
	}

	public IzborniSlotId getId() {
		return this.id;
	}

	public void setId(IzborniSlotId id) {
		this.id = id;
	}

	public Predmet getPredmet() {
		return this.predmet;
	}

	public void setPredmet(Predmet predmet) {
		this.predmet = predmet;
	}

}
