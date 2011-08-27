class CreateCoreCourseUnitYears < ActiveRecord::Migration
  def change
    create_table :core_course_unit_years do |t|
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :course_unit_type_id

      # t.timestamps
    end
    
    add_index :core_course_unit_years, [:course_unit_id, :academic_year_id], :unique => true, :name => "unique_index_course_unit_id_academic_year_id"
  end
end
