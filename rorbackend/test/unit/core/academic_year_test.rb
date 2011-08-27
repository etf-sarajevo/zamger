require 'test_helper'

class Core::AcademicYearTest < ActiveSupport::TestCase
  # Only one year must be current
  test "only_one_current_year" do
    @academic_year_4 = (Core::AcademicYear).new(:name => "Godina 4", :current => true)
    @academic_year_4.save
    if (Core::AcademicYear).where(:current => true).count == 1
      @academic_year_4.destroy
      assert true
    else
      @academic_year_4.destroy
      assert false
    end
  end
  
  # 
  test "name_unique" do
    assert_raise ActiveRecord::RecordNotUnique do
      academic_year_duplicate = (Core::AcademicYear).new(:name => "Godina 1", :current => true)
      academic_year_duplicate.save
    end
  end
  
  test "length_less_than_or_equal_to_20_more" do
    academic_year = (Core::AcademicYear).new(:name => "Godina kada se rodio Hasan Salihamidzic", :current => true)
    if academic_year.valid?
      assert false
    else
      assert true
    end
  end
  
  test "length_less_than_or_equal_to_20_exact" do
    academic_year = (Core::AcademicYear).new(:name => "Godina kada se rodio", :current => true)
    if academic_year.valid?
      assert true
    else
      assert false
    end
  end
  
  test "length_less_than_or_equal_to_20_less" do
    academic_year = (Core::AcademicYear).new(:name => "Godina kada se", :current => true)
    if academic_year.valid?
      assert true
    else
      assert false
    end
  end
  
  
  def teardown
  end
end
