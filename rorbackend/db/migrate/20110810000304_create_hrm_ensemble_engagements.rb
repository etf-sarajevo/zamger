class CreateHrmEnsembleEngagements < ActiveRecord::Migration
  def change
    create_table :hrm_ensemble_engagements do |t|
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :person_id
      t.integer :engagement_status_id

      # t.timestamps
    end
    
    add_index :hrm_ensemble_engagements, [:person_id, :course_unit_id, :academic_year_id], :name => "unique_index_person_id_course_unit_id_academic_year_id"
  end
end
