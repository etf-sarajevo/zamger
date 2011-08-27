class CreateCoreAccessLevels < ActiveRecord::Migration
  def change
    create_table :core_access_levels do |t|
      t.integer :person_id
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.enum :access_level, :limit => ['teacher', 'super_assistent', 'assistent']

      t.timestamps
    end
  end
end
