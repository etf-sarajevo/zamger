package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

/**
 * KolizijaId generated by hbm2java
 */
public class KolizijaId implements java.io.Serializable {

	private int student;
	private int akademskaGodina;
	private int semestar;
	private int predmet;

	public KolizijaId() {
	}

	public KolizijaId(int student, int akademskaGodina, int semestar,
			int predmet) {
		this.student = student;
		this.akademskaGodina = akademskaGodina;
		this.semestar = semestar;
		this.predmet = predmet;
	}

	public int getStudent() {
		return this.student;
	}

	public void setStudent(int student) {
		this.student = student;
	}

	public int getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(int akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public int getSemestar() {
		return this.semestar;
	}

	public void setSemestar(int semestar) {
		this.semestar = semestar;
	}

	public int getPredmet() {
		return this.predmet;
	}

	public void setPredmet(int predmet) {
		this.predmet = predmet;
	}

}
