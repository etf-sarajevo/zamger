package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

/**
 * AutotestRezultatId generated by hbm2java
 */
public class AutotestRezultatId implements java.io.Serializable {

	private int autotest;
	private int student;

	public AutotestRezultatId() {
	}

	public AutotestRezultatId(int autotest, int student) {
		this.autotest = autotest;
		this.student = student;
	}

	public int getAutotest() {
		return this.autotest;
	}

	public void setAutotest(int autotest) {
		this.autotest = autotest;
	}

	public int getStudent() {
		return this.student;
	}

	public void setStudent(int student) {
		this.student = student;
	}

}
