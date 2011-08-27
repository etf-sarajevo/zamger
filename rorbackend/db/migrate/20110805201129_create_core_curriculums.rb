class CreateCoreCurriculums < ActiveRecord::Migration
  def change
    create_table :core_curriculums do |t|
      t.integer :for_year
      t.integer :programme_id
      t.integer :semester
      t.integer :course_unit_id
      t.boolean :mandatory
      
      # t.timestamps
    end
  end
end
