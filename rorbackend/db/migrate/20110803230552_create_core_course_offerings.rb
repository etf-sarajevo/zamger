class CreateCoreCourseOfferings < ActiveRecord::Migration
  def change
    create_table :core_course_offerings do |t|
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :programme_id
      t.integer :semester
      t.boolean :mandatory

      # t.timestamps
    end
  end
end
