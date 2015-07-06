package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * Programskijezik generated by hbm2java
 */
public class Programskijezik implements java.io.Serializable {

	private int id;
	private String naziv;
	private String geshi;
	private String ekstenzija;
	private String ace;
	private String kompajler;
	private String opcijeKompajlera;
	private String opcijeKompajleraDebug;
	private String debugger;
	private String profiler;
	private String opcijeProfilera;

	public Programskijezik() {
	}

	public Programskijezik(int id, String naziv, String geshi,
			String ekstenzija, String ace, String kompajler,
			String opcijeKompajlera, String opcijeKompajleraDebug,
			String debugger, String profiler, String opcijeProfilera) {
		this.id = id;
		this.naziv = naziv;
		this.geshi = geshi;
		this.ekstenzija = ekstenzija;
		this.ace = ace;
		this.kompajler = kompajler;
		this.opcijeKompajlera = opcijeKompajlera;
		this.opcijeKompajleraDebug = opcijeKompajleraDebug;
		this.debugger = debugger;
		this.profiler = profiler;
		this.opcijeProfilera = opcijeProfilera;
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public String getGeshi() {
		return this.geshi;
	}

	public void setGeshi(String geshi) {
		this.geshi = geshi;
	}

	public String getEkstenzija() {
		return this.ekstenzija;
	}

	public void setEkstenzija(String ekstenzija) {
		this.ekstenzija = ekstenzija;
	}

	public String getAce() {
		return this.ace;
	}

	public void setAce(String ace) {
		this.ace = ace;
	}

	public String getKompajler() {
		return this.kompajler;
	}

	public void setKompajler(String kompajler) {
		this.kompajler = kompajler;
	}

	public String getOpcijeKompajlera() {
		return this.opcijeKompajlera;
	}

	public void setOpcijeKompajlera(String opcijeKompajlera) {
		this.opcijeKompajlera = opcijeKompajlera;
	}

	public String getOpcijeKompajleraDebug() {
		return this.opcijeKompajleraDebug;
	}

	public void setOpcijeKompajleraDebug(String opcijeKompajleraDebug) {
		this.opcijeKompajleraDebug = opcijeKompajleraDebug;
	}

	public String getDebugger() {
		return this.debugger;
	}

	public void setDebugger(String debugger) {
		this.debugger = debugger;
	}

	public String getProfiler() {
		return this.profiler;
	}

	public void setProfiler(String profiler) {
		this.profiler = profiler;
	}

	public String getOpcijeProfilera() {
		return this.opcijeProfilera;
	}

	public void setOpcijeProfilera(String opcijeProfilera) {
		this.opcijeProfilera = opcijeProfilera;
	}

}
