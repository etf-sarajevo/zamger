class CreateCoreCourseUnitTypeScoringElements < ActiveRecord::Migration
  def change
    create_table :core_course_unit_type_scoring_elements do |t|
      t.integer :course_unit_type_id
      t.integer :scoring_element_id

      t.timestamps
    end
  end
end
