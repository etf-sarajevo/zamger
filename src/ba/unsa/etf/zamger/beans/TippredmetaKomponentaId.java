package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * TippredmetaKomponentaId generated by hbm2java
 */
public class TippredmetaKomponentaId implements java.io.Serializable {

	private int tippredmeta;
	private int komponenta;

	public TippredmetaKomponentaId() {
	}

	public TippredmetaKomponentaId(int tippredmeta, int komponenta) {
		this.tippredmeta = tippredmeta;
		this.komponenta = komponenta;
	}

	public int getTippredmeta() {
		return this.tippredmeta;
	}

	public void setTippredmeta(int tippredmeta) {
		this.tippredmeta = tippredmeta;
	}

	public int getKomponenta() {
		return this.komponenta;
	}

	public void setKomponenta(int komponenta) {
		this.komponenta = komponenta;
	}

	public boolean equals(Object other) {
		if ((this == other))
			return true;
		if ((other == null))
			return false;
		if (!(other instanceof TippredmetaKomponentaId))
			return false;
		TippredmetaKomponentaId castOther = (TippredmetaKomponentaId) other;

		return (this.getTippredmeta() == castOther.getTippredmeta())
				&& (this.getKomponenta() == castOther.getKomponenta());
	}

	public int hashCode() {
		int result = 17;

		result = 37 * result + this.getTippredmeta();
		result = 37 * result + this.getKomponenta();
		return result;
	}

}
