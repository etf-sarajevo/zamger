package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * Kolizija generated by hbm2java
 */
public class Kolizija implements java.io.Serializable {

	private KolizijaId id;
	private Predmet predmet;
	private AkademskaGodina akademskaGodina;
	private Osoba osoba;

	public Kolizija() {
	}

	public Kolizija(KolizijaId id, Predmet predmet,
			AkademskaGodina akademskaGodina, Osoba osoba) {
		this.id = id;
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.osoba = osoba;
	}

	public KolizijaId getId() {
		return this.id;
	}

	public void setId(KolizijaId id) {
		this.id = id;
	}

	public Predmet getPredmet() {
		return this.predmet;
	}

	public void setPredmet(Predmet predmet) {
		this.predmet = predmet;
	}

	public AkademskaGodina getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(AkademskaGodina akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public Osoba getOsoba() {
		return this.osoba;
	}

	public void setOsoba(Osoba osoba) {
		this.osoba = osoba;
	}

}
