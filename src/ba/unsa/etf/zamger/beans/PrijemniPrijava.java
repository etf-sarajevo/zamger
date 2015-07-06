package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * PrijemniPrijava generated by hbm2java
 */
public class PrijemniPrijava implements java.io.Serializable {

	private PrijemniPrijavaId id;
	private int brojDosjea;
	private boolean nacinStudiranja;
	private int studijPrvi;
	private int studijDrugi;
	private int studijTreci;
	private int studijCetvrti;
	private boolean izasao;
	private double rezultat;

	public PrijemniPrijava() {
	}

	public PrijemniPrijava(PrijemniPrijavaId id, int brojDosjea,
			boolean nacinStudiranja, int studijPrvi, int studijDrugi,
			int studijTreci, int studijCetvrti, boolean izasao, double rezultat) {
		this.id = id;
		this.brojDosjea = brojDosjea;
		this.nacinStudiranja = nacinStudiranja;
		this.studijPrvi = studijPrvi;
		this.studijDrugi = studijDrugi;
		this.studijTreci = studijTreci;
		this.studijCetvrti = studijCetvrti;
		this.izasao = izasao;
		this.rezultat = rezultat;
	}

	public PrijemniPrijavaId getId() {
		return this.id;
	}

	public void setId(PrijemniPrijavaId id) {
		this.id = id;
	}

	public int getBrojDosjea() {
		return this.brojDosjea;
	}

	public void setBrojDosjea(int brojDosjea) {
		this.brojDosjea = brojDosjea;
	}

	public boolean isNacinStudiranja() {
		return this.nacinStudiranja;
	}

	public void setNacinStudiranja(boolean nacinStudiranja) {
		this.nacinStudiranja = nacinStudiranja;
	}

	public int getStudijPrvi() {
		return this.studijPrvi;
	}

	public void setStudijPrvi(int studijPrvi) {
		this.studijPrvi = studijPrvi;
	}

	public int getStudijDrugi() {
		return this.studijDrugi;
	}

	public void setStudijDrugi(int studijDrugi) {
		this.studijDrugi = studijDrugi;
	}

	public int getStudijTreci() {
		return this.studijTreci;
	}

	public void setStudijTreci(int studijTreci) {
		this.studijTreci = studijTreci;
	}

	public int getStudijCetvrti() {
		return this.studijCetvrti;
	}

	public void setStudijCetvrti(int studijCetvrti) {
		this.studijCetvrti = studijCetvrti;
	}

	public boolean isIzasao() {
		return this.izasao;
	}

	public void setIzasao(boolean izasao) {
		this.izasao = izasao;
	}

	public double getRezultat() {
		return this.rezultat;
	}

	public void setRezultat(double rezultat) {
		this.rezultat = rezultat;
	}

}
